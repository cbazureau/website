<?php

/**
 * Controlleur home
 * @package website
 */
class HomeController
{
	/**
	 * Gestion du controlleur
	 * @return string
	 */
	public static function main()
	{
		$ihm = \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");
		
		return $ihm->twig->loadTemplate('home.html')->render(array());
	}

}