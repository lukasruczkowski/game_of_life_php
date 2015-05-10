<?php

require_once './World.php';

// parse XML input file
$xmlInput = file_get_contents('./input.xml', true);
$parsedXml = new SimpleXMLElement($xmlInput, LIBXML_NOCDATA);
$xmlToJson = json_encode($parsedXml);
$xmlToArray = json_decode($xmlToJson, true);

// create a new World with organisms
$world = new World($xmlToArray['world'], $xmlToArray['organisms']['organism']);

// set debug mode
//$world->setDebugMode();

// iterate life steps in the World and get final state of the World
$finalState = $world->iterate();

// generate XML output with final state of the World
generateXmlOutput($xmlToArray['world'], $finalState);


/**
 * Format the data into output XML file.
 *
 * @param  array $settings
 * @param  array $world
 * @return void
 */
function generateXmlOutput($settings, $world)
{
	$dom = new \DOMDocument('1.0', 'UTF-8');
	$dom->formatOutput = true;

	$lifeElement = $dom->createElement('life');
	$dom->appendChild($lifeElement);

	$worldElement = $dom->createElement('world');
	$lifeElement->appendChild($worldElement);

	$worldElement->appendChild($dom
		->createElement('cells', (int)$settings['cells']));
	$worldElement->appendChild($dom
		->createElement('species', (int)$settings['species']));
	$worldElement->appendChild($dom
		->createElement('iterations', (int)$settings['iterations']));

	$organismsElement = $dom->createElement('organisms');
	$lifeElement->appendChild($organismsElement);

	foreach ($world as $organisms) {
		foreach ($organisms as $organism) {
			if ( ! is_null($organism)) {
				$organismElement = $dom->createElement('organism');
				$organismsElement->appendChild($organismElement);
				$organismElement->appendChild($dom
					->createElement('x_pos', $organism->getX()));
				$organismElement->appendChild($dom
					->createElement('y_pos', $organism->getY()));
				$organismElement->appendChild($dom
					->createElement('species', $organism->getSpecies()));
			}
		}
	}

	$dom->save( __DIR__ . '/output.xml');
}
