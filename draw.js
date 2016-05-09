function hexgrid(id, tiles, center, size)
{
	var canvas = document.getElementById('board');
	if (canvas.getContext)
	{
		var ctx    = canvas.getContext('2d');
		var height = size * 2;
		var width  = Math.sqrt(3)/2 * height;
		yStart = center[0];
		xStart = center[1];

		len = tiles.length;
		for (var i = 0; i < len; i++)
		{
			n = tiles[i];
			type = n.tile;
			if (type !== "void")
			{	
				p = n.pos[0];
				q = n.pos[1];
				y = yStart + (q * height * 3/4);
				x = xStart + ((q/2 + p) * width);

				border = { "ne" : n.ne, "nw" : n.nw, "w"  : n.w, "e"  : n.e, "sw" : n.sw, "se" : n.se };
				hexagon(ctx, [x, y], border, colorsheet(type, n.state), size);	
			}
		}
	}
}

function array_hexgrid(id, tiles, center, size)
{
	var canvas = document.getElementById('board');
	if (canvas.getContext)
	{
		var ctx    = canvas.getContext('2d');
		var height = size * 2;
		var width  = Math.sqrt(3)/2 * height;
		xStart = center[0];
		yStart = center[1];

		len = tiles.length;
		for (var q = 0; q < len; q++)
		{
			y    = yStart + (q * height * 3/4);
			qlen = tiles[q].length
			for (p = 0; p < qlen; p++)
			{
				x        = xStart + ((q/2 + p) * width);
				t        = tiles[q][p];
				tileType = t.tile;

				if (tileType !== "void")
				{
				    hexagon(ctx, [x, y], "", colorsheet(tileType, t.state), size);				
				}
			}
		}
	}
}


function hexagon(ctx, point, border, colors, size)
{
	ctx.fillStyle   = colors.fill;
	ctx.strokeStyle = colors.line;
	hexafill(ctx, point[0], point[1], size);
	hexaline(ctx, point[0], point[1], size, 1);
}

function hexaline(ctx, cenx, ceny, size, width)
{
	ctx.beginPath();
	ctx.lineWidth = width;
	for(var i = 0; i < 7; i++)
	{
		var ang = 2 * Math.PI / 6 * (i + 0.5);
		var x   = cenx + size * Math.cos(ang);
		var y   = ceny + size * Math.sin(ang);

		if (i === 0)
		{
			ctx.moveTo(x, y);
		}
		else 
		{
			ctx.lineTo(x, y);
		}
	}
	ctx.closePath();
	ctx.stroke();
}

function hexafill(ctx, cenx, ceny, size)
{
	ctx.beginPath();
	for(var i = 0; i < 7; i = i + 1)
	{
		var ang = 2 * Math.PI / 6 * (i + 0.5);
		var x   = cenx + size * Math.cos(ang);
		var y   = ceny + size * Math.sin(ang);

		if (i === 0)
		{
			ctx.moveTo(x, y);
		}
		else 
		{
			ctx.lineTo(x, y);
		}
	}
	ctx.fill();
	ctx.closePath();
}

function colorsheet(tile, state)
{
	colors = {};

	switch(tile)
	{
		case "land": 
		case "plains":
		case "grass":
			colors['fill'] = "hsl(116, 45%, 65%)";
			colors['line'] = "hsl(116, 45%, 75%)";
			break;

		case "shrub":
			colors['fill'] = "hsl(116, 45%, 60%)";
			colors['line'] = "hsl(116, 45%, 70%)";
			break;

		case "crag":
			colors['fill'] = "hsl(116, 45%, 50%)";
			colors['line'] = "hsl(116, 45%, 60%)";
			break;

		case "hills":
		case "forest":
			colors['fill'] = "hsl(116, 45%, 45%)";
			colors['line'] = "hsl(116, 45%, 55%)";
			break;

		case "desert":
		case "sand":
			colors['fill'] = "hsl(47, 45%, 80%)";
			colors['line'] = "hsl(47, 45%, 90%)";
			break;

		case "dune":
			colors['fill'] = "hsl(47, 45%, 75%)";
			colors['line'] = "hsl(47, 45%, 85%)";
			break;

		case "mountain":
		case "ridge":
			colors['fill'] = "hsl(47, 45%, 50%)";
			colors['line'] = "hsl(47, 45%, 60%)";
			break;

		case "peak":
			colors['fill'] = "hsl(47, 45%, 45%)";
			colors['line'] = "hsl(47, 45%, 55%)";
			break;


		// water colors
		case "coast":
		case "reef":
			colors['fill'] = "hsl(186, 30%, 80%)";
			colors['line'] = "hsl(186, 30%, 90%)";
			break;

		case "lagoon":
			colors['fill'] = "hsl(186, 30%, 75%)";
			colors['line'] = "hsl(186, 30%, 85%)";
			break;

		case "tide":
			colors['fill'] = "hsl(186, 30%, 70%)";
			colors['line'] = "hsl(186, 30%, 80%)";
			break;

		case "sea":
		case "blue":
			colors['fill'] = "hsl(186, 30%, 65%)";
			colors['line'] = "hsl(186, 30%, 75%)";
			break;

		case "water":
		case "ocean":
		case "briny":
			colors['fill'] = "hsl(186, 30%, 60%)";
			colors['line'] = "hsl(186, 30%, 70%)";
			break;

		case "depths":
			colors['fill'] = "hsl(186, 30%, 55%)";
			colors['line'] = "hsl(186, 30%, 65%)";
			break;

		// error check colors
		default:
			colors['fill'] = "hsl(186, 100%, 0%)";
			colors['line'] = "hsl(186, 100%, 25%)";
			break;
	}

	return colors;
}


