<?php

/**
 * Class Organism
 */
class Organism
{
	/**
	 * State KILL
	 *
	 * @var string
	 */
	const STATE_KILL = 'KILL';

	/**
	 * State GIVE BIRTH
	 *
	 * @var string
	 */
	const STATE_GIVE_BIRTH = 'GIVE_BIRTH';

	/**
	 * State SKIP
	 *
	 * @var string
	 */
	const STATE_SKIP = 'SKIP';

	/**
	 * x position
	 *
	 * @var int
	 */
	private $_x;

	/**
	 * y position
	 *
	 * @var int
	 */
	private $_y;

	/**
	 * Species type
	 *
	 * @var string
	 */
	private $_species;

	/**
	 * Alive organism
	 *
	 * @var bool
	 */
	private $_alive;

	/**
	 * Next state of organism
	 *
	 * @var array
	 */
	private $_nextState = array(
		'state' => null,
		'species' => null,
	);


	/**
	 * Constructor
	 *  - creates a new organism
	 *
	 * @param int    $x
	 * @param int    $y
	 * @param string $species
	 * @param bool   $isAlive
	 */
	public function __construct($x, $y, $species, $isAlive)
	{
		$this->_x = $x;
		$this->_y = $y;
		$this->_species = $species;
		$this->_alive = $isAlive;
	}


	/**
	 * Gives birth the organism
	 *
	 * @param  string $species
	 * @return void
	 */
	public function giveBirth($species)
	{
		if ( ! $this->_alive) {
			$this->_alive = true;
			$this->_species = $species;
		}
	}


	/**
	 * Returns x position
	 *
	 * @return int
	 */
	public function getX()
	{
		return $this->_x;
	}


	/**
	 * Returns y position
	 *
	 * @return int
	 */
	public function getY()
	{
		return $this->_y;
	}


	/**
	 * Returns species of organism
	 *
	 * @return string
	 */
	public function getSpecies()
	{
		return $this->_species;
	}


	/**
	 * Checks if organism is alive
	 *
	 * @return bool
	 */
	public function isAlive()
	{
		return $this->_alive;
	}


	/**
	 * Prepare organism to next state
	 *
	 * @param  string $nextState
	 * @param  string $species
	 * @return void
	 */
	public function prepareNextState($nextState, $species)
	{
		$this->_nextState['state'] = $nextState;
		$this->_nextState['species'] = $species;
	}


	/**
	 * Returns next state of organism
	 *
	 * @return array
	 */
	public function getNextState()
	{
		return $this->_nextState;
	}


	/**
	 * Reset next state of organism
	 *
	 * @return void
	 */
	public function resetNextState()
	{
		$this->_nextState = array(
			'state' => null,
			'species' => null,
		);
	}
}
