<?php

/**
 * Controlleur blog
 * @package website
 */
namespace controller;
class BlogController
{
	/**
	 * Gestion du controlleur
	 * @return string
	 */
	public static function main()
	{
		$ihm = \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");
		
		return $ihm->twig->loadTemplate('blog.html')->render(array());
	}

}