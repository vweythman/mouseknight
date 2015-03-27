<?php
	function generate_map($rows, $columns)
	{
		// intialize
		$map = init_map($rows, $columns);

		// establish land
		structure_map($map);

		// create states
		//map_states($map);

		return $map;
	}


	/* INIT_MAP
	*   --  creates a randomized map based on given amount of 
	*       rows and columns
	*************************************************************/
	function init_map($rows, $columns)
	{
		// setup 
		$map      = array();                              # ARRAY - initialized map
		$q_start  = 0;                                    # INT   - row starting value
		$q_stop   = $rows - 1;                            # INT   - row ending value
		$col_quin = $columns / 5;                         # INT   - length of col_quins
		$water    = array("land"  => .3, "water" => .7);  # ARRAY - tile values and their weights
		$land     = array("land"  => .55, "water" => .45);  # ARRAY - tile values and their weights

		// randomize map
		for($q = $q_start; $q < $q_stop; $q++)
		{
			$p_start = 0 - (($q - ($q % 2)) / 2);
			$p_stop  = $columns + $p_start;

			for($p = $p_start; $p < $p_stop; $p++)
			{
				// make tile
				$at_ends = $p < $p_start + $col_quin || $p > $p_stop - $col_quin;
				$type    = ($at_ends) ?  weighted_random_value($water) : weighted_random_value($land);
				//$type    = random_value(array_keys($water));
				$tile    = new Tile($type);

				// find neighbors
				$tile->neighborhood($q, $p, $q_start, $q_stop - 1, $p_start, $p_stop - 1);

				// place tile
				$map[$q][$p] = $tile;
			}
		}
		return $map;
	}

	/* STRUCTURE_MAP
	*   --  structures internal divisions
	*************************************************************/
	function structure_map(&$map)
	{
		// demarcate map
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				$tile->demarcate($map);
			}
		}

		// rezone map
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				$tile->rezone($map);
			}
		}

		// diversify map
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				$tile->diversify($map);
			}
		}
	}

	function map_states(&$map)
	{
		$types = array("color1", "color2", "color3", "color4");

		// randomize states
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				if ($tile->area() == "land")
				{
					$choice      = random_value($types);
					$tile->state = $choice;			
				}
			}
		}
		// setup land
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				$tile->make_territory($map);
			}
		}

		// setup land
		foreach($map as $q => $row)
		{
			foreach ($row as $q => $tile)
			{
				$tile->find_borders($map);
			}
		}
	}
?>