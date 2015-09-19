<?php

/**
 * Controlleur cv
 * @package website
 */
namespace controller;
class CvController
{
	/**
	 * Gestion du controlleur
	 * @return string
	 */
	public static function main()
	{
		$ihm =  \core\IHM::getInstance();
		$ihm->log->Debug("[".__METHOD__."] Debut de fonction");

		header("Content-type:application/pdf");
		header("Content-Disposition:attachment;filename='CV.Cedric.Bazureau.pdf'");
		readfile(BASE_APP."app/doc/cv.pdf");
		exit;
	}

}