<?php
	include 'tile.php';
	include 'tileset.php';
	include 'map.php';

	$mapHeight = 523;
	$mapWidth  = 1290;

	$tileRadius = 7;
	$tileHeight = $tileRadius * 2;
	$tileWidth  = sqrt(3)/2 * $tileHeight;
	$mapRows    = ceil($mapHeight / $tileHeight * 4/3) + 1;
	$mapCols    = ceil($mapWidth / $tileWidth);

	function convert_map($map)
	{
		$list  = array();
		$start = key(end($map));
		reset($map);
		foreach($map as $q => $line)
		{
			$offset = count($line);
			foreach ($line as $p => $point)
			{
				$tile   = $point->region();
				$state  = $point->state();
				$border = $point->border();
				$b      = array();

				foreach ($border as $key => $value) {
					$b[] = "\"$key\": $value";
				}
				$draw =  implode(", ", $b);


				$list[] = "{\"tile\" : \"$tile\", \"state\" : \"$state\", \"pos\" : [$p, $q], $draw}";
			}
		}
		return $list;
	}

	$map     = generate_map($mapRows, $mapCols);
	$con_map = convert_map($map);

?>
<!DOCTYPE html>
<html>
<head>
	<title>Mouse Knight</title>
	<style>
	body {
		margin: 0;
	}
	h1 {
		margin: 10px;
	}
	canvas {
		border: 1px solid black;
		margin: 10px auto;
		display: block;
	}
	</style>
	<script src="draw.js"></script>
</head>
<body>

	<h1>Mouse Knight Tile Demo</h1>
	<?php 
		 echo "<pre>";
		 //print_r($con_map);
		 echo "</pre>";
	?>
	<canvas id="board" width="<?php echo ($mapWidth + $tileRadius); ?>" height="<?php echo ($mapHeight + $tileRadius); ?>"></canvas>
	<script>

		var tilemap = [<?php echo implode(", ", $con_map); ?>]
		hexgrid("board", tilemap, [11, 5], <?php echo $tileRadius; ?>);

	</script>

</body>
</html>