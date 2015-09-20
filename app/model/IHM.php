<?php

/**
 * Classe IHM
 * @package core
 */
namespace core;
class IHM
{
	var $post = array();
	var $get = array();
	var $cookie = array();
	var $uri = "";
	
	/**
	* Variables de configuration
	* @var array 
	*/
	var $config = null;

	/**
	* Objet Log
	* @var object 
	*/
	var $log    = null;
	
	/**
	* Instance Twig
	* @var object 
	*/
	var $twig = null;

	/**
	* Instance de la classe
	* @var object 
	*/
	private static $_instance;

	/**
	 * Retourne l'instance de l'IHM
	 * Implémentation du pattern singleton
	 * @return object IHM Instance de l'IHM
	 */
	public static function getInstance($force=false) 
	{
		if (!isset(self::$_instance) || $force) 
		{
			self::$_instance = new IHM();
			self::$_instance->start();
		}
		return self::$_instance;
	}

	/**
	 * Retourne l'instance du gestionnaire de template
	 * @return object Twig Instance du gestionnaire de template
	 */
	public function getTwig()
	{
		return $this->twig;
	}


	/**
	 * Retourne le login/identifiant de la personne connectée
	 * @return string le login/identifiant de la personne connectée
	 */
	public function getId()
	{
		return "Inconnu";
	}

	/**
	 * Constructeur
	 */
	private function __construct ()
	{

		try {
			$this->config = new \core\ConfigLoader(BASE_APP."app/config/commun.ini");
		} catch(Exception $e) {
			// Inutile d'aller plus loin
			die("Problème de chargement de la conf : ".$e->getCode()." : ".$e->getMessage());
		}
	}

	/**
	 * Lancement de la construction de la classe IHM
	 */
	private function start()
	{
		// Initialisation du moteur de logs
		$this->log = new \core\Log ();
		
		// Pas besoin de session pour l'instant
		// $this->startSession();
		
		// Initialisation du moteur de templates Twig
		\Twig_Autoloader::register();
		$loader = new \Twig_Loader_Filesystem(BASE_APP.$this->getConfigByKey("twig.rep.view"));
		$this->twig = new \Twig_Environment($loader, array(
		    'cache' => ($this->getConfigByKey("twig.rep.cache")!=false)?BASE_APP.$this->getConfigByKey("twig.rep.cache"):false,
		));
	}

	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $name
	 */
	public function getConfigByKey($name)
	{
		try {
			return $this->config->getValueOfKey($name);
		}
		catch (Exception $e)
		{
			die("Problème: ".$e->getCode()." : ".$e->getMessage());
		}
		
	}

	/**
	* Permet de détruire la session
	* @return void
	*/
	public function startSession()
	{
		//return true;
		$this->log->Debug("[".__METHOD__."] Start de la session");
		
		session_id();
		session_name("PHPSESSID_CB");
		session_start();
	}

	/**
	* Permet de détruire la session
	* @return void
	*/
	public function resetSession()
	{
		$this->log->Debug("[".__METHOD__."] Reset de la session");
		session_unset();
		session_destroy();
		$this->startSession();
	}

	/**
	 * Error Handler pour catcher les erreurs
	 * @param string $errno Numéro d'erreur
	 * @param string $errstr Message d'erreur
	 * @param string $errfile Fichier contenant l'erreur
	 * @param string $errline Numéro de ligne
	 * @return boolean true
	 */
	public function IHM_ErrorHandler($errno, $errstr, $errfile, $errline)
	{
		switch ($errno) 
		{
			case E_USER_ERROR:
				$this->log->DexterMetier(__FUNCTION__,'E_USER_ERROR',$errstr.";".$errfile.":".$errline,"");	
				if (MODE!='PROD')
				{
					echo "Fatal error on line $errline in file $errfile";
				}
				exit(1);
				break;

			case E_USER_WARNING:
				$this->log->DexterMetier(__FUNCTION__,'E_USER_WARNING',$errstr.";".$errfile.":".$errline,"");	
				break;

			case E_USER_NOTICE:
				$this->log->Debug("[IHM_ErrorHandler][NOTICE] ".$errstr.";".$errfile.":".$errline);	
				break;

			default:
				break;
		}

		/* Don't execute PHP internal error handler */
		return true;
	}

}
