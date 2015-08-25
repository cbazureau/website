<?php
/**
 * @package core
 */

/**
 * classe ConfigLoader
 * Permet de charger des fichiers de configuration au format ini et de récupérer les variables
 * Necessite d'avoir une classe IHM
 * <code>
 * // -------------------------------------------------------------------------------------
 * // Exemple de fichier de conf fichier1.properties :
 * // -------------------------------------------------------------------------------------
 * [COMMUN]
 * 
 * ;Ceci est un commentaires
 * domain.status.creating.value=0
 * domain.status.creating.send.value=1
 * domain.status.creating.ack.value=2
 * webservices.url="tete1";
 * webservices.bss.url="tata";
 * 
 * [DEV]
 * 
 * webservices.url="tete2";
 * webservices.oss.url="titi";
 * 
 * 
 * [RECETTE]
 * 
 * webservices.url="tete3";
 * webservices.oss.url="tutu";
 * 
 * // -------------------------------------------------------------------------------------
 * // Exemple (Chargement dans la classe IHM dans une variable $config) :
 * // -------------------------------------------------------------------------------------
 * $listeFichiersConf[] = "path/du/fichier1.properties";
 * $listeFichiersConf[] = "path/du/fichier2.properties";
 * try {
 *      $this->config = new ConfigLoader($listeFichiersConf,"RECETTE");
 * } catch(Exception $e) 
 * {
 *      // Inutile d'aller plus loin
 *      die("Problème de chargement de la conf : ".$e->getCode()." : ".$e->getMessage()); 
 * }
 * // -------------------------------------------------------------------------------------
 * // Exemple (Utiliser une variable de conf dans une fonction) :
 * // -------------------------------------------------------------------------------------
 * $ihm = IHM::getInstance();
 * 
 * $ma_valeur = $ihm->config->getValueOfKey("webservices.oss.url");
 * echo $ma_valeur; 
 *	// tutu
 * 
 * $mon_tableau = $ihm->config->getValueOfKey("webservices");
 * print_r($mon_tableau); 
 * 	// array(
 * 	//			"url" => "tete3",
 * 	//			"oss" => array("url" => "tutu"),
 * 	//			"bss" => array("url" => "tata")
 * 	//		)
 * 
 * </code>
 */
namespace core;
class ConfigLoader {
	
	private $configFilesPath;
	private $globalConfigFileArray;
	private $mergedConfigFileArray;
	private $mergedSubbedConfigFileArray;
	private $overLoadingSubbedConfigFileArray;
	private $objectInitialized;
	
	/**
	 * Fonction __construct Constructor
	 * @param mixed $configFilesPath Path to the files where the data are.
	 * @param string $plateforme Load specific plateforme keys.
	 * @return exception on failure.
	 */
	public function __construct($configFilesPath=null, $plateforme=null) {

		// No configuration file available for now.
		// Do nothing.
		if(is_null($configFilesPath))
			return true;
		
		$this->configFilesPath=is_array($configFilesPath) ? $configFilesPath : array($configFilesPath);
		
		// We want to avoid duplicate configuration file name.
		// It's useless to load 2 times or more the same file.
		$this->configFilesPath=array_unique($this->configFilesPath);
		$this->configFilesPath=array_slice($this->configFilesPath, 0);
		
		$this->globalConfigFileArray=array();
		
		// Loading each file for first treatment step.
		foreach($this->configFilesPath as $configFilePath) {
			if(file_exists($configFilePath)===false)
				throw new Exception("Impossible to find configuration file: $configFilePath", 404);
				
			$tmpArray=array();
			$tmpArray=parse_ini_file($configFilePath, true);
			
			if($tmpArray===false)
				throw new Exception("Impossible to load configuration file: $configFilePath", 500);
				
			// Make one unique configuration file in memory from multiple files
			$this->globalConfigFileArray=$this->arrayMergeRecursiveDistinct($this->globalConfigFileArray, $tmpArray);
			
			unset($tmpArray);
		}
		
		// We just want to take care of part of configuration files related to
		// the plateforme we're currently using.
		// We also overload value in COMMUN section by the one in PLATEFORME section.
		// These values can ultimately be overloaded by calling overLoadValueOfKey function.
		if($plateforme!=null) $this->mergedConfigFileArray=array_merge($this->globalConfigFileArray["COMMUN"], $this->globalConfigFileArray[$plateforme]);
		else $this->mergedConfigFileArray=$this->globalConfigFileArray["COMMUN"];
		
		$this->mergedSubbedConfigFileArray=array();
		
		// Switch from plain key text to sub leveled array by sub-key
		$this->subLevelArrayKey();
		
		if(!is_array($this->overLoadingSubbedConfigFileArray))
			$this->overLoadingSubbedConfigFileArray=array();
			
		// Now we can use public function as there is at least
		// one configuration file loaded.
		$this->objectInitialized=true;
	}
	
	/**
	 * Fonction addConfigFile Constructor
	 * @param mixed $confiFilePath Path to the file where the data are.
	 * @param string $plateforme Load specific plateforme keys.
	 * @return exception on failure
	 */
	public function addConfigFile($configFilePath, $plateforme=null) {
		if(is_null($configFilePath))
			throw new Exception("Impossible to find configuration file: $configFilePath", 403);
		$this->configFilesPath[]=$configFilePath;
		
		self::__construct($this->configFilesPath, $plateforme);
	}
	
	/**
	 * Fonction subLevelArrayKey Create a sub leveled array 
	 * for each part of each key
	 */
	private function subLevelArrayKey() {
		foreach($this->mergedConfigFileArray as $iniKey => $iniValue) {
			$iniSubKeys=explode(".", $iniKey);
			$tmpArray=array();
			$tmpArrayFirstPos=&$tmpArray;
			foreach($iniSubKeys as $iniSubKey) {
				$tmpArray[$iniSubKey]="";
				$tmpArray=&$tmpArray[$iniSubKey];
			}
			$tmpArray=$iniValue;
			$this->mergedSubbedConfigFileArray=$this->arrayMergeRecursiveDistinct($this->mergedSubbedConfigFileArray, $tmpArrayFirstPos);
			unset($tmpArray);
		}
	}
	
	/**
	 * Fonction arrayMergeRecursiveDistinct Merge recursively 2 arrays 
	 * and only keep last value for already existing keys.
	 * 
	 * @param $array1 First array
	 * @param $array2 Second array
	 * @return array Result of merging of 2 arrays in parameters
	 */
	private function arrayMergeRecursiveDistinct(array &$array1, array &$array2) {
		$merged=$array1;
		
		foreach ($array2 as $key => &$value) {
			if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
				$merged[$key]=$this->arrayMergeRecursiveDistinct($merged[$key],$value);
			} else {
				$merged[$key]=$value;
			}
		}
		return $merged;
	}
	
	/**
	 * Fonction getValueOfKey Get corresponding value
	 * or set of values from a key (set of values are packed in one array)
	 * @param string $iniKey A valide key (partkey1.partkey2...).
	 * @param bool $initialValue get initial value before any overloading.
	 * @return mixed A single value or an array of end part of key and corresponding values.
	 */
	public function getValueOfKey($iniKey=NULL, $initialValue=false) {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
			
		$checkKey=true;
		$checkKey2=true;
		if(!is_null($iniKey) && $iniKey!='') {
			// Check if the key exist in config file array and in overload key array. 
			$iniKeyArrayPath="['".str_replace(".", "']['", $iniKey)."']";
			$checkKey=$this->arrayKeyExistsRecursive(explode(".", $iniKey), $this->mergedSubbedConfigFileArray);
			$checkKey2=$this->arrayKeyExistsRecursive(explode(".", $iniKey), $this->overLoadingSubbedConfigFileArray);
		} else {
			// We want the full configuration file array.
			return $this->arrayMergeRecursiveDistinct($this->mergedSubbedConfigFileArray, $this->overLoadingSubbedConfigFileArray);
		}
		// First check if key is available in overload key array
		// and return that if true.
		// Else return value of config file array if it exist.
		// Else exception.
		if($checkKey2===true && $initialValue===false) {
			$returnCode=eval('$result=is_array($this->overLoadingSubbedConfigFileArray'.$iniKeyArrayPath.');');
			if(!is_null($returnCode))
				throw new Exception('Internal error!', 500);
			if($result===false)
				$returnCode=eval('$result=$this->overLoadingSubbedConfigFileArray'.$iniKeyArrayPath.';');
			else
				$returnCode=eval('$result=$this->arrayMergeRecursiveDistinct($this->mergedSubbedConfigFileArray'.$iniKeyArrayPath.', $this->overLoadingSubbedConfigFileArray'.$iniKeyArrayPath.');');

		} else if($checkKey===false) {
			throw new Exception("Key '".$iniKey."' doesn't exist!", 400);
		} else {
			$returnCode=eval('$result=$this->mergedSubbedConfigFileArray'.$iniKeyArrayPath.';');
		}
		if(is_null($returnCode))
			return $result;
		else
			throw new Exception('Internal error!', 500);
	}
	
	/**
	 * Fonction arrayKeyExistsRecursive Check if a key exist in a multi-dimensional array
	 * @param $iniKeyArray branch of initial array
	 * @param $mergedSubbedConfigFileArray initial array
	 * @return bool true if key exis, false if not
	 */
	private function arrayKeyExistsRecursive($iniKeyArray, $mergedSubbedConfigFileArray) {
		if(isset($iniKeyArray[0]) && array_key_exists($iniKeyArray[0], $mergedSubbedConfigFileArray) && is_array($mergedSubbedConfigFileArray[$iniKeyArray[0]])) {
			$tmpVar=$iniKeyArray[0];
			$iniKeyArray=array_slice($iniKeyArray, 1);
			return $this->arrayKeyExistsRecursive($iniKeyArray, $mergedSubbedConfigFileArray[$tmpVar]);
		} elseif((isset($iniKeyArray[0]) && array_key_exists($iniKeyArray[0], $mergedSubbedConfigFileArray)) || (count($iniKeyArray)==0 && count($mergedSubbedConfigFileArray)!=0)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Fonction overLoadValueOfKey Change the value of one key.
	 * This function is not to add root key but just to adapt value of
	 * one key if needed.
	 * @param $iniKey The key for the corresponding value to adapt
	 * @param $value The new value for correspondig key
	 * @return bool true on success, exception on failure
	 */
	public function overLoadValueOfKey($iniKey, $value) {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
			
		if(is_null($iniKey) || $iniKey=='')
			throw new Exception('Key name is empty!', 404);
		
		//Check if the key exist
		$this->getValueOfKey($iniKey);
		
		$iniKeyArrayPath="['".str_replace(".", "']['", $iniKey)."']";
		$returnCode=eval('$this->overLoadingSubbedConfigFileArray'.$iniKeyArrayPath.'=$value;');
		if(is_null($returnCode))
			return true;
		else
			throw new Exception('Internal error!', 500);
	}
	
	/**
	 * Fonction resetOverLoadingValueOfKey Cancel all previous
	 * overloading of value of key.
	 */
	public function resetOverLoadingValueOfKey() {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
			
		unset($this->overLoadingSubbedConfigFileArray);
		$this->overLoadingSubbedConfigFileArray=array();
	}
	
	/**
	 * Fonction resetOneOverLoadingValueOfKey Cancel one previous
	 * overloading of value of key.
	 * @param string $iniKey the key to un-overload.
	 * @return bool true on success, exception else.
	 */
	public function resetOneOverLoadingValueOfKey($iniKey) {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
			
		if(is_null($iniKey) || $iniKey=='')
			throw new Exception('Key name is empty!', 404);
		
		$checkKey=$this->arrayKeyExistsRecursive(explode(".", $iniKey), $this->overLoadingSubbedConfigFileArray);
		
		if($checkKey===false)
			throw new Exception("Key '".$iniKey."' doesn't exist!", 400);
		
		$returnCode=$this->cleanUnOverLoadedKey(explode(".", $iniKey));
		if($returnCode===true)
			return true;
		else
			throw new Exception('Internal error!', 500);
	}
	
	/**
	 * Fonction cleanUnOverLoadedKey Properly clear the tree after
	 * cancelling one overload.
	 * @param array $iniArrayKey the key to clean.
	 * @return bool true on success, false else.
	 */
	private function cleanUnOverLoadedKey($iniArrayKey) {
		if(!is_array($iniArrayKey))
			return false;

		$spareIniArrayKey=$iniArrayKey;
		$tmpArray=$this->overLoadingSubbedConfigFileArray;
		
		foreach($iniArrayKey as $subIniArrayKey) {
			$tmpArray=is_array($tmpArray["$subIniArrayKey"]) ? $tmpArray["$subIniArrayKey"] : $tmpArray;
		}
		
		while(count($tmpArray)<=1 && count($iniArrayKey)>0) {
			$iniArrayKey=array_slice($iniArrayKey, 0, count($iniArrayKey)-1);
			$tmpArray=&$this->overLoadingSubbedConfigFileArray;
			foreach($iniArrayKey as $subIniArrayKey) {
				$tmpArray=&$tmpArray["$subIniArrayKey"];
			}
			
		}
		unset($tmpArray[$spareIniArrayKey[count($iniArrayKey)]]);
		
		return true;
	}
	
	/**
	 * Fonction isOverLoadedKey Check if one key has been
	 * overloaded previously.
	 * @param string $iniKey the key to check.
	 * @return bool true if exist, false if not.
	 */
	public function isOverLoadedKey($iniKey) {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
			
		if(is_null($iniKey) || $iniKey=='')
			throw new Exception('Key name is empty!', 404);
		
		return($this->arrayKeyExistsRecursive(explode(".", $iniKey), $this->overLoadingSubbedConfigFileArray));
	}
	
	/**
	 * Fonction listOverLoadedKeys Check if one key has been
	 * overloaded previously.
	 * @return array of all overloaded keys with values.
	 */
	public function listOverLoadedKeys() {
		if($this->objectInitialized!==true)
			throw new Exception("No configuration file loaded currently!", 403);
		
		return $this->overLoadingSubbedConfigFileArray;
	}
}
