<?php
	
	function sum_array($a1, $a2)
	{
		$sum   = array();
		foreach (array_keys($a1 + $a2) as $key)
		{
			$sum[$key] = (isset($a1[$key]) ? $a1[$key] : 0) + (isset($a2[$key]) ? $a2[$key] : 0);
		}
		return $sum;
	}

	function weighted_random_value($array)
	{
		// setup
		$choices = array_keys($array);
		$weights = array_values($array);
		$prob    = mt_rand() / mt_getrandmax();

		// init
		$choice  = "";
		$weight  = array_pop($weights);

		// cycle
		while ($weight < $prob)
		{
			$choice = array_pop($choices);
			$weight = (count($weights) > 0) ? $weight + array_pop($weights) : 1;
		}

		// select
		return array_pop($choices);
	}


	/* RANDOM_VALUE
	*   --  uses an Mersenne Twister to select a random value
	*       from an array.
	*************************************************************/
	function random_value($array)
	{
		return $array[mt_rand(0, count($array) - 1)];
	}

	/* WEIGHTED_PICK
	*   --  selects choosen value or random value from array 
	*       using a Mersenne Twister
	*************************************************************/
	function weighted_pick($array, $choice, $weight)
	{
		// setup
		$other = array_diff($array, [$choice]);
		$other = array_values($other);
		$prob  = mt_rand() / mt_getrandmax();

		// choose
		if ($weight > $prob) {
			return $choice;
		}
		elseif (count($other) > 1) {
			return random_value($other);
		}
		else {
			return array_pop($other);
		}
	}

?>