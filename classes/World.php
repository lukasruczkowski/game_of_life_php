<?php

require_once 'Organism.php';


/**
 * Class World
 */
class World
{
	/**
	 * Dimension of the square "world"
	 *
	 * @var int
	 */
	private $cells;

	/**
	 * Number of distinct species
	 *
	 * @var int
	 */
	private $species;

	/**
	 * Number of iterations
	 *
	 * @var int
	 */
	private $iterations;

	/**
	 * Matrix of organisms
	 *
	 * @var array
	 */
	private $world = array();

	/**
	 * Debug mode
	 *
	 * @var bool
	 */
	private $debugMode = false;


	/**
	 * Constructor
	 *
	 * Creates World for organisms
	 *  - set dimension of the square "world"
	 *  - set number of distinct species
	 *  - set number of iterations
	 *  - creates all organisms
	 *
	 * @param  array $settings
	 * @param  array $organisms
	 */
	public function __construct($settings, $organisms)
	{
		// set settings for world
		$this->cells = $settings['cells'];
		$this->species = $settings['species'];
		$this->iterations = $settings['iterations'];

		// prepare world
		for ($i = 1; $i <= $this->cells; $i++) {
			for ($j = 1; $j <= $this->cells; $j++) {
				$this->world[$i][$j] = null;
			}
		}

		// add all organisms into world
		$species = array();
		foreach ($organisms as $organism) {
			// check count of species
			if ( ! in_array($organism['species'], $species)) {
				if (count($species) < $this->species) {
					$species[] = $organism['species'];
				} else {
					// skip organism if the count of species is more than it
					// was defined
					continue;
				}
			}

			$this->_addOrganism(
				$organism['x_pos'],
				$organism['y_pos'],
				$organism['species'],
				true
			);
		}
	}


	/**
	 * Iterates steps in the World and returns the final state of the World
	 *
	 * @return array
	 * @throws Exception
	 */
	public function iterate()
	{
		// debug
		if ($this->debugMode) {
			echo "Start of iteration\n";
			echo "-----------------------------\n";
		}

		$i = 0;
		while ($i < $this->iterations) {
			$i++;

			// debug
			if ($this->debugMode) {
				echo "Step $i:\n\n";
			}

			$this->_checkAllElements();
		}

		// debug
		if ($this->debugMode) {
			echo "End of iteration\n";
		}

		return $this->world;
	}


	/**
	 * Set debug mode
	 *
	 * @return void
	 */
	public function setDebugMode()
	{
		$this->debugMode = true;
	}


	/**
	 * Adds a new organism into World
	 *
	 * @param  int    $x
	 * @param  int    $y
	 * @param  string $species
	 * @param  bool   $isAlive
	 * @return void
	 * @throws Exception
	 */
	private function _addOrganism($x, $y, $species, $isAlive)
	{
		// check if position of organism is in range of the World
		if ($x > $this->cells || $x < 1) {
			throw new Exception('x position of organism is out of range');
		}
		if ($y > $this->cells || $y < 1) {
			throw new Exception('y position of organism is out of range');
		}

		// check if organism does not exist in this element
		if ( ! empty($this->_getOrganism($x, $y))) {
			// choose randomly one of the organism that will live in this
			// element (existing or new)
			// if random value will be TRUE then existing organism will stay
			if (rand(0, 1)) {
				return;
			}
		}

		// add new element into world
		$this->world[$x][$y] = new Organism($x, $y, $species, $isAlive);
	}


	/**
	 * Returns organism according to entered position
	 *
	 * @param  int      $x
	 * @param  int      $y
	 * @return Organism
	 */
	private function _getOrganism($x, $y)
	{
		// return null if organism does not exist
		if ( ! isset($this->world[$x][$y])) {
			return null;
		}

		return $this->world[$x][$y];
	}


	/**
	 * Kill organism according to entered position
	 *
	 * @param  int  $x
	 * @param  int  $y
	 * @return void
	 * @throws Exception
	 */
	private function _killOrganism($x, $y)
	{
		$organism = $this->world[$x][$y];

		// check if organism exist and it is alive
		if (isset($organism) && $organism->isAlive()) {
			$this->world[$x][$y] = null;
		} else {
			throw new Exception("organism does not exist or it is not alive");
		}
	}


	/**
	 * Returns all alive neighbors of element according to entered position
	 *
	 * @param  int   $x
	 * @param  int   $y
	 * @return array
	 */
	private function _getAliveNeighbours($x, $y)
	{
		// array of neighbours
		$neighbours = array();

		// array of directions for all possible neighbours
		$directions = array(
			// above
			array(-1, 1),
			array(0, 1),
			array(1, 1),
			// sides
			array(-1, 0),
			array(1, 0),
			// below
			array(-1, -1),
			array(0, -1),
			array(1, -1)
		);

		// check all possible neighbours
		foreach ($directions as $direction) {
			$possibleNeighbour = $this->_getOrganism(
				($x + $direction[0]),
				($y + $direction[1])
			);

			if ( ! empty($possibleNeighbour) && $possibleNeighbour->isAlive()) {
				$neighbours[] = $possibleNeighbour;
			}
		}

		return $neighbours;
	}


	/**
	 * Checks all elements in the World and performs the appropriate operations
	 * (kill, give birth) according to rules for World
	 *
	 * @return void
	 * @throws Exception
	 */
	private function _checkAllElements()
	{
		// pass all the elements in the world
		foreach ($this->world as $x => $x_line) {
			foreach ($x_line as $y => $organism) {
				// get alive neighbours of the element
				$aliveNeighbours = $this->_getAliveNeighbours($x, $y);

				// get species of the neighbours
				$speciesOfNeighbours =
					$this->_getCountOfNeighboursSpecies($aliveNeighbours);

				// prepare organism to kill if the conditions are fulfilled
				if ( ! is_null($organism)
					&& $organism->isAlive()
					&& isset($speciesOfNeighbours[$organism->getSpecies()])
				) {
					// x = count of neighbours with the same species
					// if (x < 2 || x >=4) => kill organism
					if ($speciesOfNeighbours[$organism->getSpecies()] < 2
						|| $speciesOfNeighbours[$organism->getSpecies()] >= 4
					) {
						// set next state of the organism to KILL
						$organism->prepareNextState(Organism::STATE_KILL, null);
						continue;
					}
				}

				// prepare organism to give a birth if the conditions are
				// fulfilled
				if (is_null($organism)) {
					// check count of species of alive neighbours
					foreach ($speciesOfNeighbours as $species => $count) {
						// if count of species of alive neighbours is equal 3
						// then the new organism will born
						if ($count == 3) {
							// add new organism to world but set alive to false
							// for now
							$this->_addOrganism($x, $y, $species, false);

							// set next state of a new organism to GIVE BIRTH
							$this->_getOrganism($x, $y)
								->prepareNextState(
									Organism::STATE_GIVE_BIRTH, $species);
							break;
						}
					}
					continue;
				}

				// alive organism will survive
				if ( ! is_null($organism)) {
					$organism->prepareNextState(Organism::STATE_SKIP, null);
				}
			}
		}

		// switch all organisms to the next state
		$this->_nextStateOfWorld();
	}


	/**
	 * Switch all organisms in the World to the next state
	 *
	 * @return void
	 * @throws Exception
	 */
	private function _nextStateOfWorld()
	{
		// pass all the elements in the world
		foreach ($this->world as $x => $x_line) {
			foreach ($x_line as $y => $organism) {
				// switch all existing organism to next state
				if ( ! is_null($organism)) {
					// get next state of organism
					$nextState = $organism->getNextState();

					// switch organism to next state
					switch($nextState['state']) {
						case Organism::STATE_SKIP :
							// debug
							if ($this->debugMode) {
								echo " " . $organism->getSpecies() . " ";
							}
							break;
						case Organism::STATE_KILL :
							// debug
							if ($this->debugMode) {
								echo "-" . $organism->getSpecies() . " ";
							}
							$this->_killOrganism($x, $y);
							break;
						case Organism::STATE_GIVE_BIRTH :
							// debug
							if ($this->debugMode) {
								echo "+" . $organism->getSpecies() . " ";
							}
							$organism->giveBirth($nextState['species']);
							break;
						default :
							throw new Exception("invalid state");
					}

					// reset next state for next iteration
					$organism->resetNextState();
				} else {
					// debug
					if ($this->debugMode) {
						echo " . ";
					}
				}
			}
			// debug
			if ($this->debugMode) {
				echo "\n\n";
			}
		}
		// debug
		if ($this->debugMode) {
			echo "-----------------------------\n";
		}
	}


	/**
	 * Get count of species of the alive neighbours
	 *
	 * @param  array $neighbours
	 * @return array
	 */
	private function _getCountOfNeighboursSpecies($neighbours)
	{
		$count = array();

		foreach ($neighbours as $neighbour) {
			if ( ! isset($count[$neighbour->getSpecies()])) {
				$count[$neighbour->getSpecies()] = 1;
			} else {
				$count[$neighbour->getSpecies()] += 1;
			}
		}

		return $count;
	}
}
