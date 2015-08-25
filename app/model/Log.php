<?php

/**
 * Classe Log
 * @package core
 */
namespace core;

define("IHM_DEBUG",   7);
define("IHM_NOTICE",  6);
define("IHM_INFO",    5);
define("IHM_WARNING", 4);
define("IHM_ERROR",   3);
define("IHM_CRIT",    2);
define("IHM_ALERT",   1);
define("IHM_EMERG",   0);

class Log
{
	var $array_level = array(   IHM_DEBUG => "DEBUG",
								IHM_NOTICE  => "NOTICE",
								IHM_INFO    => "INFO",
								IHM_WARNING => "WARNING",
								IHM_ERROR   => "ERROR",
								IHM_CRIT    => "CRIT",
								IHM_ALERT   => "ALERT",
								IHM_EMERG   => "EMERG");

	var $config_debug_by_ip   = null;
	var $config_rep           = null;
	
	var $config_filedebug   = null;
	var $config_fileerror   = null;
	var $config_fileinfo    = null;

	var $IP_addr     = null;

	/**
	* Création du moteur de log
	* @param $ihm object IHM
	* @return void
	*/
	function __construct ()
	{
		
		$ihm = \core\IHM::getInstance();
		
		$this->config_debug_by_ip   = $ihm->getConfigByKey("log.debug_by_ip");
		$this->config_rep           = BASE_APP.$ihm->getConfigByKey("log.rep");
		$this->config_info          = $ihm->getConfigByKey("log.info");
		
		
		$this->config_filedebug     = $this->config_rep."debug-".date("Y-m-d").".log";
		$this->config_fileerror     = $this->config_rep."error-".date("Y-m-d").".log";
		$this->config_fileinfo      = $this->config_rep."info-".date("Y-m-d").".log";
		
		// On regarde dans l'ordre HTTP_X_FORWARDED_FOR, puis HTTP_ORIGCLIENTADDR, puis REMOTE_ADDR
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$this->IP_addr = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		elseif(isset($_SERVER["HTTP_ORIGCLIENTADDR"]))
		{
			$this->IP_addr = $_SERVER["HTTP_ORIGCLIENTADDR"];
		}
		else $this->IP_addr = $_SERVER["REMOTE_ADDR"];
	}

	/**
	* Renvoi l'utilisateur connecté
	* @return string Chaine Correspondant à l'utilisateur connecté
	*/
	protected function getConnectedUser()
	{
		$ihm = \core\IHM::getInstance();
		return $ihm->getId();
	}

	/**
	* Renvoi true si le debug est actif (même partiellement)
	* @return boolean true si le debug est actif 
	*/
	public function isDebugActif()
	{
		return ($this->config_debug_by_ip!="");
	}


	/**
	* Ecrit un message dans un fichier de debug
	* @param string $message Message à logger
	* @param int $type Type de message
	* @return void
	*/
	protected function MessageFichierDebug ($message, $type)
	{
		if($this->config_debug_by_ip=="*" || $this->config_debug_by_ip==$this->IP_addr)
		{
			$sess = $this->getConnectedUser();
			$text = $this->array_level[$type];
			$fd   = fopen($this->config_filedebug, 'a+');
			$logs = date("d-m-Y H:i:s").";".$text.";".$this->IP_addr.";".$sess.";".$message."\r\n";
			fputs($fd, $logs);
			fclose($fd);
		}
	}

	/**
	* Ecrit un message dans un fichier de info
	* @param string $message Message à logger
	* @param int $type Type de message
	* @return void
	*/
	protected function MessageFichierInfo ($message, $type)
	{
		if($this->config_info)
		{
			$sess = $this->getConnectedUser();
			$text = $this->array_level[$type];
			$fd   = fopen($this->config_fileinfo, 'a+');
			$logs = date("d-m-Y H:i:s").";".$text.";".$this->IP_addr.";".$sess.";".$message."\r\n";
			fputs($fd, $logs);
			fclose($fd);
		}
	}

	/**
	* Ecrit un message dans un fichier de erreur
	*
	* @param string $message Message à logger
	* @param $type int : Type de message
	* @return void
	*/
	protected function MessageFichierError ($message, $type)
	{
		$sess = $this->getConnectedUser();
		$text = $this->array_level[$type];
		$fd   = fopen($this->config_fileerror, 'a+');
		$logs = date("d-m-Y H:i:s").";".$text.";".$this->IP_addr.";".$sess.";".$message."\r\n";
		fputs($fd, $logs);
		fclose($fd);
	}

	/**
	* Message DEBUG
	* @param string $message Message à logger
	* @return void
	*/
	public function Debug ($message)
	{
		$this->logGen(IHM_DEBUG, $message);
	}

	/**
	* Message NOTICE
	* @param string $message Message à logger
	* @return void
	*/
	public function Notice ($message)
	{
		$this->logGen(IHM_NOTICE, $message);
	}

	/**
	* Message INFO
	* @param string $message Message à logger
	* @return void
	*/
	public function Info ($message)
	{
		$this->logGen(IHM_INFO, $message);
	}

	/**
	* Message WARNING
	* @param string $message Message à logger
	* @return void
	*/
	public function Warning ($message)
	{
		$this->logGen(IHM_WARNING, $message);
	}

	/**
	* Message ERROR
	* @param string $message Message à logger
	* @return void
	*/
	public function Error ($message)
	{
		$this->logGen(IHM_ERROR, $message);
	}

	/**
	* Message CRIT
	* @param string $message Message à logger
	* @return void
	*/
	public function Crit ($message)
	{
		$this->logGen(IHM_CRIT, $message);
	}

	/**
	* Message ALERT
	* @param string $message Message à logger
	* @return void
	*/
	public function Alert ($message)
	{
		$this->logGen(IHM_ALERT, $message);
	}

	/**
	* Message EMERG
	* @param string $message Message à logger
	* @return void
	*/
	public function Emerg ($message)
	{
		$this->logGen(IHM_EMERG, $message);
	}

	/** Fonction LogGen Générique
	* @param string $niveau Niveau de log ou type de ligne
	* @param string $message Message à logger
	* @return void
	*/
	protected function LogGen ($niveau, $message)
	{
		if(in_array($niveau, array(IHM_DEBUG)))                                          	$this->MessageFichierDebug($message, $niveau);
		if(in_array($niveau, array(IHM_NOTICE, IHM_INFO)))                                  $this->MessageFichierInfo($message, $niveau);
		if(in_array($niveau, array(IHM_WARNING, IHM_ERROR, IHM_CRIT, IHM_ALERT, IHM_EMERG)))$this->MessageFichierError($message, $niveau);
	}

	/**
	* Envoi de mail de suivi + Log de même niveau
	* @param string $sujet Sujet du mail
	* @param string $mail Contenu du mail
	* @param string $niveau Niveau de log
	* @return boolean True Si mail bien expédié
	*/
	protected function Mail ($sujet, $mail, $niveau)
	{
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: robot-mc@bbox.fr\r\n';
		$return = @mail($this->config["Destinataires"], $sujet, $mail, $headers);
		
		$this->logGen ($niveau, $sujet);
		
		return $return;
	}

	/**
	* Fonction Static de mise en page des array pour les logs
	* @param array $array Tableau en entrée
	* @return string Tableau mis en forme
	*/
	public function TemplateArray ($array)
	{
		// $html = serialize($array);
		// $html = convert_array_to_string($array);
		$html = print_r($array, true);
		return $html;
	}
}
