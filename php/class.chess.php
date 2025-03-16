<?PHP

class chess
	{
	public function __construct($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{		
		$this->id = uniqid('', true);
		
		$this->blank = "_"; # blank square, no pieces 
		
		$this->start 	= microtime(true);
		$this->now		= microtime(true);
		$this->micro	= 10000; # 1/100th of a second, time to wait for response 
		
		$this->payload 	= array();
		$this->debug 	= false;
		
		$this->board = array(); # by color, by rank/file ??
		
		


		$this->lets = "abcdefgh"; 
			$this->letA = str_split($this->lets);
		$this->nums = "12345678";
			$this->numA = str_split($this->nums);
			
		$this->setupBoard($fen);
		$this->whiteAttacks = $this->blackAttacks = array(); # for castling/pins
			
		
		}
	
	public function isBlank($loc="c3", $who="w")
		{
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		echo"\n\n loc: $loc \t\t who: $who \t\t piece: $piece \t\t color: $color \n\n";
		if($piece == $this->blank) 
			{ 
			if($who == "w") { $this->whiteAttacks[] = $loc; }
			if($who == "b") { $this->blackAttacks[] = $loc; }
			return "blank"; 
			}
		
		if($who == $color)
			{
			return "self";
			} else 	{ 
					if($who == "w") { $this->whiteAttacks[] = $loc; }
					if($who == "b") { $this->blackAttacks[] = $loc; }
					return "enemy"; 
					}
		}
	
	public function attackedSquares() {}
	public function isPinned() {} # can't move ... if not present, would king be under attack ... remove the piece, calculate attacks ... if King is attacked, you can't move this piece ... 
	public function getPossibleMoves($loc = "e2", $prev = "")
		{
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		echo("\n piece: ".$piece."\t color: ".$color."\t\t file: ".$file."\t rank: ".$rank."\t\t\t loc: ".$loc." \t\t prev: ".$prev."\n\n");
				
		
		$moves 		= array();
		$captures 	= array();  # subset of moves
		# {color}Attacks = moves 
		switch(strtoupper($piece))
			{
			# pawn (enpassant? this requires move history $prev)
			default:
			case "P":
				# if white and on rank=2
				# if black and on rank=7, can move forward 1/2 (if not blocked)
				# can capture to ++ or -+
			
			break;
			
			case "B":
				{
				# loop through all options, stop on capture or edge of board or own piece 
				# four directions ++,--,+-,-+
				# as you review the directions, stop if you run into a piece ... that last move is valid if the location contains opponent piece 
				# track which squares are under attack by white/black
				
				# get starting indexes 
				$ridx = array_search($rank, $this->numA);
				$fidx = array_search($file, $this->letA);
				
				# echo"\n\n ridx: $ridx \t\t fidx: $fidx \n\n"; exit;
				
				################ generic moves ################
				$min = 0; $max = 7;
				
				
				## ++ 
				$x = $fidx;
				$y = $ridx;
				for($i = $fidx; $i < $max; $i++)
					{
					$x++; if($x > $max) { break; }
					$y++; if($y > $max) { break; }
					$loc = $this->letA[$x] . $this->numA[$y];
					$status = $this->isBlank($loc, $color);
					if($status == "blank") { $moves[] =  $loc; }
					if($status == "self") { break; }
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						break;
						}					
					}
				## +- 
				$x = $fidx;
				$y = $ridx;
				for($i = $fidx; $i > 0; $i--)
					{
					$x--; if($x < $min) { break; }
					$y++; if($y > $max) { break; }
					$loc = $this->letA[$x] . $this->numA[$y];
					$status = $this->isBlank($loc, $color);
					if($status == "blank") { $moves[] =  $loc; }
					if($status == "self") { break; }
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						break;
						}	
					}
				## -+ 
				$x = $fidx;
				$y = $ridx;
				for($i = $fidx; $i < $max; $i++)
					{
					$x++; if($x > $max) { break; }
					$y--; if($y < $min) { break; }
					$loc = $this->letA[$x] . $this->numA[$y];
					$status = $this->isBlank($loc, $color);
					if($status == "blank") { $moves[] =  $loc; }
					if($status == "self") { break; }
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						break;
						}	
					}
					
				## -- 
				$x = $fidx;
				$y = $ridx;
				for($i = $fidx; $i > 0; $i--)
					{
					$x--; if($x < $min) { break; }
					$y--; if($y < $min) { break; }
					$loc = $this->letA[$x] . $this->numA[$y];
					$status = $this->isBlank($loc, $color);
					if($status == "blank") { $moves[] =  $loc; }
					if($status == "self") { break; }
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						break;
						}	
					}
				
				#print_r($moves);
				#print_r($captures);
				
				}
			break;
			
			case "R":
			
			break;
			
			case "K":
			
			break;
			
			case "Q":
			
			break;
			
			case "N":
			
			break;
			}
		
		}
	
	public function getRank($loc = "e2")
		{
		$dat 	= str_split($loc);
		return $dat[1];
		}
	public function getFile($loc = "e2")
		{
		$dat 	= str_split($loc);
		return $dat[0];
		}
	
	public function getColorAtPosition($loc = "e2")
		{
		$piece = $this->getPieceAtPosition($loc);
		return $this->getPieceColor($piece);
		}
	public function getPieceAtPosition($loc = "e2")
		{
		$dat 	= str_split($loc);
		$piece 	= $this->board["file"][ $dat[0] ] [ $dat[1] ];
		return $piece;	
		}
	public function getPieceColor($piece)
		{		
		$color = "";
		if($piece !== $this->blank) # blank 
			{
			$color = ctype_upper($piece) ? "b" : "w"; # black or white 	
			}
		return $color;	
		}
		
	public function toUCI($san = "e4") 
		{
		$move = array(); # let's store the full information 
		# was the move from e2 or e3 (lookup options on board rank/file)
		
		
		}
		
		
	public function fromUCI($uci = "e2e4") {}
		
	public function setupBoard($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{
		
		$tmp = explode("/", $fen);
		$tmp2 = explode(" ", $tmp[7]);
		$data = array_merge(array_slice($tmp,0,7), array($tmp2[0]));
		foreach($data as $k=>$v)
			{
			$value = str_split($v);
			$new = "";
			foreach($value as $key=>$val)
				{
				if(is_numeric($val))
					{
					$new .= str_repeat($this->blank,$val); # empty square
					} else { $new .= $val; }
				}
			$data[$k] = $new;
			}
		
		foreach($this->numA as $num)
			{
			$row = $data[$num - 1];
			$dat = str_split($row);
			foreach($this->letA as $k=>$let)
				{
				$what = $dat[$k];
				$this->board["rank"][$num][$let] = $this->board["file"][$let][$num] = $what;				
				}
			}		
		}
		
		
	

	public function close()
		{

		
		$this->now = microtime(true); 
		$this->time = $this->now - $this->start;
		$this->payload["id"] = $this->id;
		$this->payload["time"] = $this->time;
		}
	
	}

?>