<?php

/**
 * Classe Bootstrap
 * @package core
 */
namespace core;
class Bootstrap
{

	/**
	 * Constructeur
	 */
	public function __construct()
	{
		// Protect cookie
		ini_set('session.cookie_httponly',1);
		ini_set('session.use_only_cookies',1);

		// Definition de la racine du projet
		define("BASE_APP",__DIR__.'/../../');
		
		// Chargement des dépendances externes et de l'autoload interne
		require(BASE_APP.'vendor/autoload.php');
		spl_autoload_register(array( $this, 'autoload'));
		
		$ihm = \core\IHM::getInstance(true);

	}
	
	/**
	 * routing
	 * @param array $request_uri L'url appellée
	 * @param array $ihm->get Tableau des variables envoyées en get
	 * @param array $post Tableau des variables envoyées en post
	 * @param array $cookie Tableau des variables envoyées en cookie
	 */
	private function routing($request_uri,$get,$post,$cookie)
	{
		$ihm = \core\IHM::getInstance();
		$ihm->get = $get;
		$ihm->post = $post;
		$ihm->cookie = $cookie;
		$ihm->uri = preg_replace("/\?(.*)/", "", $request_uri);
		$ihm->get["controller"] = null; // On le recalcule
		$ihm->get["error"] = null; // On le recalcule
		$return = false;
		
		if ($ihm->uri == "/") {
			// Controller home
			$ihm->get["controller"] = "home";
			$return = true;
		} else if (preg_match('/\.(jpg|gif|png) *$/i', $ihm->uri, $matches)) {
			// Image en 404
			$ihm->get["controller"] = "error";
			$ihm->get["error"] = \controller\ErrorController::ERR_404_IMG;
			$return = false;
		} else if (preg_match('^/(.*)^', $ihm->uri, $matches)) {
			if(isset($matches[1])) {
				$tab = explode("/",$matches[1]);
				// Controller identifié
				$ihm->get["controller"] = $tab[0];
				$return = true;
			} else {
				// Controller non-identifié
				$ihm->get["controller"] = "error";
				$ihm->get["error"] = \controller\ErrorController::ERR_404;
				$return = false;
			}
		} else {
			// Controller non-identifié
			$ihm->get["controller"] = "error";
			$ihm->get["error"] = \controller\ErrorController::ERR_404;
			$return = false;
		}
		
		return array(
			"code_retour" => $return,
			"controller" => $ihm->get["controller"]
		);
		
	}

	/**
	 * Launcher du bootstrap
	 * @param array $request_uri L'url appellée
	 * @param array $ihm->get Tableau des variables envoyées en get
	 * @param array $post Tableau des variables envoyées en post
	 * @param array $cookie Tableau des variables envoyées en cookie
	 */
	public function launch($request_uri,$get,$post,$cookie)
	{
		$ihm = \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");
		
		$return_routing = $this->routing($request_uri,$get,$post,$cookie);
		$ihm->log->Debug("uri : ".$ihm->uri);
		if(!empty($ihm->get)) $ihm->log->Debug("get : ".print_r($ihm->get,true));

		// --------------------------------------------
		// Switch qui détermine le controller
		// --------------------------------------------
		$class = "\\controller\\".ucfirst($ihm->get["controller"])."Controller";
		$method = "main";
		$function = $class."::".$method;
		$ihm->log->Debug("[".__METHOD__."] On cherche le controleur : ".$function);
		
		try {
			if(method_exists($class,$method) && is_callable($function)) {
				$return = call_user_func($function);
			} else {
				$ihm->get["error"] = \controller\ErrorController::ERR_404_CONTROLLER;
				$return = \controller\ErrorController::main();
			}
		}
		catch (\Exception $e) {
			$ihm->get["error"] = \controller\ErrorController::ERR_404_CONTROLLER;
			$return = \controller\ErrorController::main();
		}
		
		// --------------------------------------------
		// Construction de la sortie
		// --------------------------------------------
		$ihm->log->Debug("[".__METHOD__."] Construction de la sortie");
		return $return;
	}


	/**
	 * Autoloader
	 * @param string $classname Le nom de la classe à charger
	 */
	private function autoload($classname)
	{
		$matrice = array(
				"core\\ConfigLoader" => "app/model/ConfigLoader.php",
				"core\\IHM" => "app/model/IHM.php",
				"core\\Log" => "app/model/Log.php"
		);
		if(isset($matrice[$classname]) 
			&& file_exists(BASE_APP.$matrice[$classname])) {
			$claspath = BASE_APP.$matrice[$classname];
			require_once($claspath);
		} else if(strpos($classname,"Controller")!==false 
					&& file_exists(BASE_APP."app/controller/".str_replace("controller\\","",$classname).".php")) {
			$claspath = BASE_APP."app/controller/".str_replace("controller\\","",$classname).".php";
			require_once($claspath);
		} else {
			throw new \Exception("Problème d'installation : Classe ".$classname." non trouvée");
		}
		
	}
	

}