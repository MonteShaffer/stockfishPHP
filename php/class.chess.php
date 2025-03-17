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
	
	public function isValidLocation($x,$y)
		{
		if($x > 7) { return false; }
		if($y > 7) { return false; }
		if($x < 0) { return false; }
		if($y < 0) { return false; }
					
		$loc = $this->letA[$x] . $this->numA[$y];
		return $loc;
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
	
	public function getBishopMoves($loc = "e2", $prev = "") 
		{
		/*    common 				*/
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		$fidx = array_search($file, $this->letA);
		$ridx = array_search($rank, $this->numA);
		
		# loop through all options, stop on capture or edge of board or own piece 
		# four directions ++,--,+-,-+
		# as you review the directions, stop if you run into a piece ... that last move is valid if the location contains opponent piece 
		# track which squares are under attack by white/black
		
		# get starting indexes 
		
		
		# echo"\n\n ridx: $ridx \t\t fidx: $fidx \n\n"; exit;
		
						
		
		## ++ 
		$x = $fidx;
		$y = $ridx;
		for($i = $fidx; $i < 7; $i++)
			{
				$x++;
				$y++;
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
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
			$x--; 
			$y++; 
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
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
		for($i = $fidx; $i < 7; $i++)
			{
			$x++; 
			$y--; 
			
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
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
			$x--; 
			$y--; 
			
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $loc;
				$captures[] = $loc; 
				break;
				}	
			}
		
		#print_r($moves);
		#print_r($captures);
		
		
		return array("moves"=>$moves,"captures"=>$captures,"protects"=>$protects);	
		}
	public function getRookMoves ($loc = "e2", $prev = "") 
		{
		/*    common 				*/
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		$fidx = array_search($file, $this->letA);
		$ridx = array_search($rank, $this->numA);
		
		
		# loop through all options, stop on capture or edge of board or own piece 
		# four directions +0,-0,0+,0-
		
		
		# echo"\n\n ridx: $ridx \t\t fidx: $fidx \n\n"; exit;
		
						
		## +0 
		$x = $fidx;
		$y = $ridx;
		for($i = $fidx; $i < 7; $i++)
			{
			$x++; 
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $loc;
				$captures[] = $loc; 
				break;
				}					
			}
		## -0 
		$x = $fidx;
		$y = $ridx;
		for($i = $fidx; $i > 0; $i--)
			{
			$x--; 
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $loc;
				$captures[] = $loc; 
				break;
				}	
			}
		## 0- 
		$x = $fidx;
		$y = $ridx;
		for($i = $ridx; $i < 7; $i++)
			{
			$y--; 
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $loc;
				$captures[] = $loc; 
				break;
				}	
			}
			
		## 0+ 
		$x = $fidx;
		$y = $ridx;
		for($i = $ridx; $i < 7; $i++)
			{
			$y++; 
			$loc = $this->isValidLocation($x,$y);
			if(!$loc){ break; }
			$status = $this->isBlank($loc, $color);
			if($status == "blank") { $moves[] =  $loc; }
			if($status == "self") { $protects[] = $loc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $loc;
				$captures[] = $loc; 
				break;
				}	
			}
		
		#print_r($moves);
		#print_r($captures);
		
		
		return array("moves"=>$moves,"captures"=>$captures,"protects"=>$protects);	
		}
	
	
	
	
	public function getPossibleMoves($loc = "e2", $prev = "")
		{
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		echo("\n piece: ".$piece."\t color: ".$color."\t\t file: ".$file."\t rank: ".$rank."\t\t\t loc: ".$loc." \t\t prev: ".$prev."\n\n");
		
		$fidx = array_search($file, $this->letA);
		$ridx = array_search($rank, $this->numA);
		
		$sign = ($color = "w") ? 1 : -1; # advance in "y" direction
				
				
		### do isPinned logic at the beginning, no moves ... return empty 
		
		$moves 		= array();
		$captures 	= array();  # subset of moves
		$protects 	= array();	# not subset of moves 
		# {color}Attacks = moves 
		switch(strtoupper($piece))
			{
			# pawn (enpassant? this requires move history $prev)
			default:
			case "P":
				# if white and on rank=2
				# if black and on rank=7, can move forward 1/2 (if not blocked)
				# can capture to ++ or -+
				
				# check squares:  forward 1/2; diagonal left/right (forward)
				
				# option 1 
				$x = $fidx; $y = $ridx + 1*$sign;
				$loc = $this->isValidLocation($x,$y);
				if($loc)
					{ 
					$status = $this->isBlank($loc, $color);
					if($status == "blank") { $moves[] =  $loc; }
					}
						
				# option 2 
				$x = $fidx; $y = $ridx + 2*$sign;
				if(($color == "w" && $rank == 2) || ($color == "b" && $rank == 7) )
					{
					$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						}
					}	
				
				# option 3 (forward / sideOne)
				$x = $fidx; $y = $ridx;
				$x++; $y += 1*$sign;
				$loc = $this->isValidLocation($x,$y);
				if($loc)
					{ 
					$status = $this->isBlank($loc, $color);
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						}
					if($status == "self")
						{
						$protects[] = $loc;
						}
					}
				
				# option 4 (forward / sideTwo)
				$x = $fidx; $y = $ridx;
				$x--; $y += 1*$sign;
				$loc = $this->isValidLocation($x,$y);
				if($loc)
					{ 
					$status = $this->isBlank($loc, $color);
					if($status == "enemy") 
						{
						$moves[] =  $loc;
						$captures[] = $loc; 
						}
					if($status == "self")
						{
						$protects[] = $loc;
						}
					}
					
				# option 5/6 forward "enpassant"
			
			break;
			
			case "B":
				return $this->getBishopMoves($loc, $prev);
			break;
			
			case "R":
				return $this->getRookMoves($loc, $prev);
			break;
			
			case "Q":
				{
				# so getMovesQueen does both above functions
				$b = $this->getBishopMoves($loc,$prev);
				$r = $this->getRookMoves($loc,$prev);
				
				$moves = array_merge($b["moves"], $r["moves"]);
				$captures = array_merge($b["captures"], $r["captures"]);
				$protects = array_merge($b["protects"], $r["protects"]);
				}
			break;
			
			case "K":
				{
				# in forloop of board, we skip K and do at the end 
				# check for castle (from FEN plus empty plus not attacking those empty squares... K or R move is already known in "move history)
				# how to do "isPinned"??? for other pieces ...
				# king has 8 possible moves ... let's first check for "is possible" in "skip KING" loop 
				# second loop will prune the first loop
				# isPinned???
				
				# first pass for King is like any other piece
				# after board is built, we do a second pass to prune possible moves (if the piece is protected ... colorAttacking the square of the piece ... OR if the square is blank by colorAttacking ... invalid move related to CHECK ...
				# do a once-removed recursion of a similar board (cloned to see for isPinned???
				
				# option 1 (-1,1) TopLeft for white
				$x = $fidx - 1; $y = $ridx + 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc; # with king, maybe not fully protected
							}
						}
				# option 2 (0,1) Top for white
				$x = $fidx; $y = $ridx + 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 3 (1,1) TopRight for white
				$x = $fidx+1; $y = $ridx + 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 4 (-1,-1) BottomLeft for white
				$x = $fidx - 1; $y = $ridx - 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 5 (0,-1) Bottom for white
				$x = $fidx; $y = $ridx - 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 6 (1,-1) BottomRight for white
				$x = $fidx+1; $y = $ridx - 1 *$sign;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 7 (-1,0) Left for white
				$x = $fidx - 1; $y = $ridx;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				# option 8 (1,0) Right for white
				$x = $fidx + 1; $y = $ridx;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				
				
				}
			break;
			
			
			
			case "N":
				{
				# check 8 squares... if empty or enemy move/capture ... this piece can jump!
						
				# option 1 
				$x = $fidx + 1; $y = $ridx + 2;
				# isValid 
				# isBlank 
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}

				
				# option 2 
				$x = $fidx + 2; $y = $ridx + 1;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 3 
				$x = $fidx + 1; $y = $ridx - 2;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 4 
				$x = $fidx + 2; $y = $ridx - 1;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 5 
				$x = $fidx - 1; $y = $ridx + 2;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 6 
				$x = $fidx - 2; $y = $ridx + 1;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 7 
				$x = $fidx - 1; $y = $ridx - 2;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
						
				# option 8 
				$x = $fidx - 2; $y = $ridx - 1;
				$loc = $this->isValidLocation($x,$y);
					if($loc)
						{ 
						$status = $this->isBlank($loc, $color);
						if($status == "blank") { $moves[] =  $loc; }
						if($status == "enemy") 
							{
							$moves[] =  $loc;
							$captures[] = $loc; 
							}
						if($status == "self")
							{
							$protects[] = $loc;
							}
						}
				
				
				
				
				
				}
			break;
			}
		
		return array("moves"=>$moves,"captures"=>$captures,"protects"=>$protects);
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