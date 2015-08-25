<?php

/**
 * Controlleur error
 * @package website
 */
class ErrorController
{
	const ERR_404 = "404";
	const ERR_404_IMG = "404_IMG";
	const ERR_404_CONTROLLER = "404_CONTROLLER";
	
	/**
	 * Gestion du controlleur
	 * @return string
	 */
	public static function main()
	{
		$ihm =  \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");
		
		return $ihm->twig->loadTemplate('error.html')->render(array());
	}

}