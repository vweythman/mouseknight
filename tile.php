<?php
	class Tile
	{
		const MAX_WEIGHT = .99;
		const MID_WEIGHT = .75;
		const MIN_WEIGHT = .54;

		# CONSTRUCTORS
		# --------------------------------------------------------------------------------
		function __construct($area) {
			$this->area     = $area;
		}

		# GETTERS
		# --------------------------------------------------------------------------------
		function area() {
			return $this->area;
		}

		function zone() {
			return isset($this->zone) ? $this->zone : $this->area();
		}

		function region() {
			return isset($this->region) ? $this->region : $this->zone();
		}

		function state()
		{
			return isset($this->state) ? $this->state : $this->area();
		}

		function border()
		{
			return isset($this->border) ? $this->border : array();
		}

		function region_map()
		{
			switch ($this->zone) {
				case "coast":
					$regions = array("reef", "lagoon");
					break;
				case "sea":
					$regions = array("blue", "tide");
					break;
				case "ocean":
					$regions = array("depths", "briny");
					break;
				case "shore":
					$regions = array("beach", "cliff");
					break;
				case "desert":
					$regions = array("dune", "sand");
					break;
				case "plains":
					$regions = array("shrub", "grass");
					break;
				case "hills":
					$regions = array("forest", "crag");
					break;
				case "mountain":
				default:
					$regions = array("peak", "ridge");
					break;
			}
			return $regions;
		}

		# SETTERS -- GEOGRAPHY
		# --------------------------------------------------------------------------------
		function neighborhood($q, $p, $q0, $qn, $p0, $pn)
		{
			// set - horizontal
			$p_left  = $p == $p0 ? $pn : $p - 1;
			$p_right = $p == $pn ? $p0 : $p + 1;
			
			// set - vertical
			$q_up    = $q - 1;
			$q_down  = $q + 1;

			// set - neighborhood
			$neighbors = array();

			// set - north
			if ($q != $q0)
			{
				$this->neighbors["nw"] = array("q" => $q_up, "p" => $p);
				$this->neighbors["ne"] = array("q" => $q_up, "p" => $p_right);
			}

			// set - parallel
			$this->neighbors["w"] = array("q" => $q, "p" => $p_left);
			$this->neighbors["e"] = array("q" => $q, "p" => $p_right);

			// set - south
			if ($q != $qn)
			{
				$this->neighbors["sw"] = array("q" => $q_down, "p" => $p_left);
				$this->neighbors["se"] = array("q" => $q_down, "p" => $p);
			}
		}

		function demarcate(&$map)
		{
			// setup types
			$tiles = array("land", "water");
			
			// find distribution
			$dist = $this->distribution($map, "area");

			// count tiles
			$land  = isset($dist["land"])  ? $dist["land"]  : 0;
			$water = isset($dist["water"]) ? $dist["water"] : 0;

			// choose tile
			if ($land > $water) {
				$this->area = weighted_pick($tiles, "land", TILE::MAX_WEIGHT);
			}
			elseif ($water > $land) {
				$this->area = weighted_pick($tiles, "water", TILE::MAX_WEIGHT);
			}

			// randomize zone
			$zones      = ($this->area == "land") ? array("plains", "hills", "mountain", "desert") : array("ocean", "sea");
			$this->zone = random_value($zones);
		}

		function rezone(&$map)
		{
			// find distribution
			$dist1 = $this->distribution($map, "area");
			$dist2 = $this->distribution($map, "zone");

			// count tiles
			$land    = isset($dist1["land"])  ? $dist1["land"]  : 0;
			$water   = isset($dist1["water"]) ? $dist1["water"] : 0;
			$varied  = $land > 0 && $water > 0;

			// setup shoreline
			if ($varied && $this->area == "water")
			{
				$this->zone = "coast";
			}
			// setup water
			elseif ($this->area == "water")
			{
				// setup tiles
				$tiles = array("ocean", "sea");
				
				// count tiles(zone)
				$ocean = isset($dist2["ocean"]) ? $dist2["ocean"] : 0;
				$sea   = isset($dist2["sea"])   ? $dist2["sea"]   : 0;

				// choose zone
				if ($ocean > $sea) {
					$this->zone = weighted_pick($tiles, "ocean", TILE::MID_WEIGHT);
				}
				elseif ($sea > $ocean) {
					$this->zone = weighted_pick($tiles, "sea", TILE::MID_WEIGHT);
				}
			}
			// setup land
			else
			{
				// setup tiles
				$tiles    = array("plains", "hills", "mountain");

				// count tiles(zone)
				$plains   = isset($dist2["plains"])   ? $dist2["plains"]   : 0;
				$hills    = isset($dist2["hills"])    ? $dist2["hills"]    : 0;
				$mountain = isset($dist2["mountain"]) ? $dist2["mountain"] : 0;
				$desert   = isset($dist2["desert"])   ? $dist2["desert"]   : 0;
				$total    = $plains + $hills + $mountain + $desert;

				if ($total > 0)
				{
					if ($mountain > 1 && $mountain / $total <= 1/3) {
						$this->zone = weighted_pick($tiles, "mountain", TILE::MIN_WEIGHT);
					}
					elseif ($hills / $total > 1/3) {
						$this->zone = weighted_pick($tiles, "hills", TILE::MAX_WEIGHT);
					}
					elseif ($plains / $total > 1/3) {
						$this->zone = weighted_pick($tiles, "plains", TILE::MAX_WEIGHT);
					}
					elseif ($desert / $total > 1/3) {
						$this->zone = weighted_pick($tiles, "desert", TILE::MAX_WEIGHT);
					}
				}
			}

			// randomize region
			$regions      = $this->region_map();
			$this->region = random_value($regions);
		}

		function diversify(&$map)
		{
			// setup tiles
			$tiles = $this->region_map();
			$c1    = $tiles[0];
			$c2    = $tiles[1];

			// setup count
			$dist  = $this->distribution($map, "region");
			$count1 = isset($dist[$c1]) ? $dist[$c1] : 0; 
			$count2 = isset($dist[$c2]) ? $dist[$c2] : 0;

			if ($this->zone == "shore" || $this->zone == "desert") {
				$weight = Tile::MIN_WEIGHT;
			}
			else {
				$weight = Tile::MAX_WEIGHT;
			}

			// choose tile
			if ($count1 > $count2) {
				$this->region = weighted_pick($tiles, $c1, $weight);
			}
			elseif ($count2 > $count1) {
				$this->region = weighted_pick($tiles, $c2, $weight);
			}
		}

		# SETTERS -- STATES
		# --------------------------------------------------------------------------------
		function make_territory(&$map)
		{
			if ($this->area() == "water")
				return false;

			// setup
			$tiles = array("color1", "color2", "color3", "color4");
			$dist  = $this->distribution($map, "state");

			// count
			$color1 = isset($dist["color1"]) ? $dist["color1"] : 0;
			$color2 = isset($dist["color2"]) ? $dist["color2"] : 0;
			$color3 = isset($dist["color3"]) ? $dist["color3"] : 0;
			$color4 = isset($dist["color4"]) ? $dist["color4"] : 0;
			$total  = $color1 + $color2 + $color3 + $color4;


			if ($total > 0)
			{
				if ($color1 / $total > 1/4) {
					$this->state = weighted_pick($tiles, "color1", TILE::MIN_WEIGHT);
				}
				elseif ($color2 / $total > 1/4) {
					$this->state = weighted_pick($tiles, "color2", TILE::MAX_WEIGHT);
				}
				elseif ($color3 / $total > 1/4) {
					$this->state = weighted_pick($tiles, "color3", TILE::MAX_WEIGHT);
				}
				elseif ($color4 / $total > 1/4) {
					$this->state = weighted_pick($tiles, "color4", TILE::MAX_WEIGHT);
				}
			}
		}
		function find_borders(&$map)
		{
			if ($this->area() == "water")
			{
				$this->border = array(
					"nw" => "false", 
					"ne" => "false", 
					"w"  => "false", 
					"e"  => "false", 
					"sw" => "false", 
					"se" => "false");
				return false;
			}

			$border   = array();
			$cardinal = array("nw", "ne", "w", "e", "sw", "se");
			foreach ($cardinal as $direction) 
			{
				if (isset($this->neighbors[$direction]))
				{
					$q = $this->neighbors[$direction]["q"];
					$p = $this->neighbors[$direction]["p"];

					if (isset($map[$q][$p]))
					{
						$tile       = $map[$q][$p];
						$is_water   = $tile->area() == "water";
						$diff_state = $tile->state() != $this->state();

						$set        = $is_water || $diff_state;
					}
					else {
						$set = true;
					}
				}
				else {
					$set = true;
				}
				
				$border[$direction] = $set ? "true" : "false";
			}

			$this->border = $border;
			return $this->border;
		}


		# COUNTERS
		# --------------------------------------------------------------------------------
		function distribution(&$map, $area)
		{
			// setup
			$count = array();

			// find
			foreach($this->neighbors as $position)
			{
				// get position
				$q = $position["q"];
				$p = $position["p"];

				// check position
				if (isset($map[$q][$p]))
				{
					// determine type
					switch ($area) {
						case 'state':
							$type = $map[$q][$p]->state();
							break;
						
						case 'area':
							$type = $map[$q][$p]->area();
							break;
						
						case 'zone':
							$type = $map[$q][$p]->zone();
							break;
						
						case 'region':
							$type = $map[$q][$p]->region();
							break;
												
						default:
							$type = $map[$q][$p]->area();
							break;
					}

					// add type
					$this->counter($count, $type);
				}
			}

			// result
			return $count;
		}

		function counter(&$count, $type)
		{
			$value        = isset($count[$type]) ? $count[$type] : 0;
			$count[$type] = $value + 1;
		}
	}
?>