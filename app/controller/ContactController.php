<?php

/**
 * Controlleur contact
 * @package website
 */
namespace controller;
class ContactController
{
	/**
	 * Gestion du controlleur
	 * @return string
	 */
	public static function main()
	{
		$ihm = \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");
		
		return $ihm->twig->loadTemplate('contact.html')->render(array("title" => "Contactez-moi"));
	}

}