<?PHP

class chess
	{
	public function __construct($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{		
		# toMove and moveNumber is embedded above in FEN...
		$this->id = uniqid('', true);
		
		
		$this->start 	= microtime(true);
		$this->now		= microtime(true);
		$this->micro	= 10000; # 1/100th of a second, time to wait for response 
		
		$this->payload 	= array();
		$this->debug 	= false;
		
		$this->history = array();
		
		
		
		$this->board = new board($fen);	
			# contains possible moves and board-related information
		
		}
	
		
	public function toUCI($san = "c3", $color="w") 
		{
		$move = array(); # let's store the full information 
		# was the move from e2 or e3 (lookup options on board rank/file)
		# e4 means the "pawn moved to e4" from where?  ???
		# you have to know the board position (setupBoard + basicAnalysis) to know this???
		
		$sA = str_split($san);
		$slen = strlen($san);
		$upperFirst = ctype_upper($sA[0]);
		$hasCapture = in_array("x",$sA);
		$hasPromotion = in_array("=",$sA); # what of a8Q, no equals
		
		
		# let's get "to" first 
		# e4 c3
		if($slen == 2) { $p = "P"; $to = $san; }
		# a8Q
		if($slen == 3 && !$upperFirst) { $p = "P"; $to = $sA[0].$sA[1]; $promote = $sA[2]; }
		# Nf3 Nc3
		if($slen == 3 && !$upperFirst) { $p = $sA[0]; $to = $sA[1].$sA[2]; }
		
		# N2e4 or Nge4
		# Ng2e4
		
		# create outside class.checkmate.php to have clones and check for advanced options castling / check / pinned piece / checkmate
		# en passant?
		
		# choices (locations and pieces)
		$locs = $this->getPossibleMoveTo($to,$color);
		foreach($locs as $loc)
			{
			# pieces are uppercase, color is known from move/half-move
			$ps = strtoupper($this->getPieceAtPosition($loc));
			if($p == $ps) { $from = $loc; break; }
			}
		
		print_r($loc);
		print_r($ps); # exit;
		
		
		}
	
	public function getPossibleMoveTo($to="e4",$color="w")
		{
		return $this->moveinfo["canMoveTo"][$color][$to];
		}
	
	
	public function isKingAttacked($color = "w")
		{
		# return false if NOT attacked = 0; otherwise, return number of attacks
		# canKingMoveTo pruning? advanced considerations, after PIN pruning ... class.checkmate.php will allow copies and clones of this class to address some of this nested logic [PRUNING]
		
		}
	
	public function fromUCI($uci = "e2e4") {}
	
		
		
	

	public function close()
		{

		
		$this->now = microtime(true); 
		$this->time = $this->now - $this->start;
		$this->payload["id"] = $this->id;
		$this->payload["time"] = $this->time;
		}
	
	}

# lookup info, get possible moves
class board
	{
	public function __construct($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{
		$this->blank = "_"; # blank square, no pieces 
		
		$this->board = array(); # by color, by rank/file ??
		$this->score = array(); # "basic" is counter
		$this->moveinfo = array(); # by color/piece 

		$this->lets = "abcdefgh"; 
			$this->letA = str_split($this->lets);
		$this->nums = "12345678";
			$this->numA = str_split($this->nums);
		
		$this->setupBoard($fen);
		$this->whiteAttacks = $this->blackAttacks = array(); # for castling/pins
		
		$this->basicAnalysis();
		
		$this->checkPins(); # prune moves from pieces that can't move due to a pin 
		$this->checkKing(); # can King castle, prune King moves (can't capture a piece that is protected at least once)
		
		}
		
	public function parseFEN($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{
		$tmp = explode("/", $fen);
		$tmp2 = explode(" ", $tmp[7]);
		$extra = trim(str_replace($tmp2[0],"",$tmp[7]));
		
		$etmp = explode(" ",$extra);
		$toMove = $etmp[0];
		$castleInfo = $etmp[1]; # KQkq down to "-" (no castling)
		$enpassantInfo = $etmp[2]; # "-" means N/A, e3 is non-passant move potential
		$halfMoveDraw = $etmp[3]; # count moves since a capture, reset to "0" when a capture occurs. when == 100, the game ends in a draw [how to track 3-position repetition? ... not part of FEN apparently] ... we can track with history of FEN ... if board FEN is the same 3 times, it is a draw...
		$fullmoveNumber = $etmp[4]; # 1w, 1b, 2w, 2b, ...
		}	
	public function setupBoard($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{
		$this->score["basic"][$this->blank] = 0;
		$this->score["basic"]["w"] = 0;
		$this->score["basic"]["b"] = 0;
		
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

				$color = $this->getPieceColor($what);
				
				$this->board["pieces"][$color][$what][] = $let.$num;
				
				$this->score["basic"][$color] += $this->basicScorePiece($what);
				}
			}		
		}
		
	public function isBlank($mloc="c3", $who="w")
		{
		$piece = $this->getPieceAtPosition($mloc);
		$color = $this->getPieceColor($piece);
		#echo"\n\n mloc: $mloc \t\t who: $who \t\t piece: $piece \t\t color: $color \n\n";
		if($piece == $this->blank) 
			{ 
			if($who == "w") { $this->whiteAttacks[] = $mloc; }
			if($who == "b") { $this->blackAttacks[] = $mloc; }
			return "blank"; 
			}
		
		if($who == $color)
			{
			return "self";
			} else 	{ 
					if($who == "w") { $this->whiteAttacks[] = $mloc; }
					if($who == "b") { $this->blackAttacks[] = $mloc; }
					return "enemy"; 
					}
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
	
	
	
	public function sameRank($A = "e2", $B = "c2")
		{
		$a = $this->getRank($A);
		$b = $this->getRank($B);
		return ($a == $b);
		}
	public function getRank($loc = "e2")
		{
		$dat 	= str_split($loc);
		return $dat[1];
		}
	public function sameFile($A = "e2", $B = "e4")
		{
		$a = $this->getFile($A);
		$b = $this->getFile($B);
		return ($a == $b);
		}
	public function getFile($loc = "e2")
		{
		$dat 	= str_split($loc);
		return $dat[0];
		}
	
	# isPinned is K and piece must be 
	#		-	 on sameRank, nothing between
	#		-	 on sameFile, nothing between 
	#		- 	 on sameDiagonal, nothing between 
	# if one of these are true, then let's clone board, remove piece and see if K has new attack
	public function sameDiagonal($A = "e2", $B = "c2")
		{
		$aR = array_search($this->getRank($A), $this->letA);
		$bR = array_search($this->getRank($B), $this->letA);
		
		$aF = array_search($this->getFile($A), $this->letA);
		$bF = array_search($this->getFile($B), $this->letA);
		
		# we need idx of rank/file
		# take abs val of subtraction of each 
		return (abs($aR-$bR) == abs($aF-$bF));
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
		$color = $this->blank;
		if($piece !== $this->blank) # blank 
			{
			$color = ctype_upper($piece) ? "b" : "w"; # black or white 	
			}
		return $color;	
		}
	
	public function attackedSquares() {}
	public function isPinned() {} # can't move ... if not present, would king be under attack ... remove the piece, calculate attacks ... if King is attacked, you can't move this piece ... 
	/*
	-is on diagonal
	-only blanks between K and piece
	-enemy B/Q is attacking piece on same diagnon 
	
	-is on rank/file
	-enemy R/Q is attacking
	
	1st pass = basicAnalysis
	2nd pass = isPinned   X is pinned by Y (e2 e4)
	3rd pass = updateMoves
	
	2+ pins = no moves
	1 pin = you can attack the attacker, is that possible 
	depends on whose turn is it ...
	
	isCheck()
	- check KingMoves
	- check who can attack attackers
	- for each of above(2) moves, clone board, do single move, do basicAnalysus,isPinn,upDateMoves and see if King is still attacked... this lists all [+] checkPossible moves.  If zero, checkmate #
	
	
	chess-json as chjson or chson = chess standard object notation
	*/
	
	
	
	public function getBishopMoves($loc = "e2", $prev = "") 
		{
		/*    common 				*/
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		$fidx = array_search($file, $this->letA);
		$ridx = array_search($rank, $this->numA);
				
		$moves = $captures = $protects = array();
		
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
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
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
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
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
			
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
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
			
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
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
		
		
		$moves = $captures = $protects = array();
		# loop through all options, stop on capture or edge of board or own piece 
		# four directions +0,-0,0+,0-
		
		
		# echo"\n\n ridx: $ridx \t\t fidx: $fidx \n\n"; exit;
		
						
		## +0 
		$x = $fidx;
		$y = $ridx;
		for($i = $fidx; $i < 7; $i++)
			{
			$x++; 
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
				break;
				}					
			}
		## -0 
		$x = $fidx;
		$y = $ridx;
		for($i = $fidx; $i > 0; $i--)
			{
			$x--; 
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
				break;
				}	
			}
		## 0- 
		$x = $fidx;
		$y = $ridx;
		for($i = $ridx; $i < 7; $i++)
			{
			$y--; 
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
				break;
				}	
			}
			
		## 0+ 
		$x = $fidx;
		$y = $ridx;
		for($i = $ridx; $i < 7; $i++)
			{
			$y++; 
			$mloc = $this->isValidLocation($x,$y);
			if(!$mloc){ break; }
			$status = $this->isBlank($mloc, $color);
			if($status == "blank") { $moves[] =  $mloc; }
			if($status == "self") { $protects[] = $mloc; break; }
			if($status == "enemy") 
				{
				$moves[] =  $mloc;
				$captures[] = $mloc; 
				break;
				}	
			}
		
		#print_r($moves);
		#print_r($captures);
		
		# these are possible moves 
		return array("moves"=>$moves,"captures"=>$captures,"protects"=>$protects);	
		}
	
	public function getPossibleMoves($loc = "e2", $prev = "")
		{
		$piece = $this->getPieceAtPosition($loc);
		$color = $this->getPieceColor($piece);
		
		$rank = $this->getRank($loc);
		$file = $this->getFile($loc);
		
		# echo("\n piece: ".$piece."\t color: ".$color."\t\t file: ".$file."\t rank: ".$rank."\t\t\t loc: ".$loc." \t\t prev: ".$prev."\n\n");
		
		$fidx = array_search($file, $this->letA);
		$ridx = array_search($rank, $this->numA);
		
		$sign = ($color == "w") ? 1 : -1; # advance in "y" direction
				
				
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
				{
				# if white and on rank=2
				# if black and on rank=7, can move forward 1/2 (if not blocked)
				# can capture to ++ or -+
				
				# check squares:  forward 1/2; diagonal left/right (forward)
				
				# option 1 
				$x = $fidx; $y = $ridx + 1*$sign;
				$mloc = $this->isValidLocation($x,$y);
				if($mloc)
					{ 
					$status = $this->isBlank($mloc, $color);
					if($status == "blank") { $moves[] =  $mloc; }
					}
						
				# option 2 
				$x = $fidx; $y = $ridx + 2*$sign;
				if(($color == "w" && $rank == 2) || ($color == "b" && $rank == 7) )
					{
					$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						}
					}	
				
				# option 3 (forward / sideOne)
				$x = $fidx; $y = $ridx;
				$x++; $y += 1*$sign;
				$mloc = $this->isValidLocation($x,$y);
				if($mloc)
					{ 
					$status = $this->isBlank($mloc, $color);
					if($status == "enemy") 
						{
						$moves[] =  $mloc;
						$captures[] = $mloc; 
						}
					if($status == "self")
						{
						$protects[] = $mloc;
						}
					}
				
				# option 4 (forward / sideTwo)
				$x = $fidx; $y = $ridx;
				$x--; $y += 1*$sign;
				$mloc = $this->isValidLocation($x,$y);
				if($mloc)
					{ 
					$status = $this->isBlank($mloc, $color);
					if($status == "enemy") 
						{
						$moves[] =  $mloc;
						$captures[] = $mloc; 
						}
					if($status == "self")
						{
						$protects[] = $mloc;
						}
					}
					
				# option 5/6 forward "enpassant"
				}
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
				
				
				# echo"\n ########### KING loc $loc color $color ########### \n";
				
				# option 1 (-1,1) TopLeft for white
				$x = $fidx - 1; $y = $ridx + 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						# echo"\n\n 1 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc; # with king, maybe not fully protected
							}
						}
				# option 2 (0,1) Top for white
				$x = $fidx; $y = $ridx + 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 2 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 3 (1,1) TopRight for white
				$x = $fidx+1; $y = $ridx + 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 3 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 4 (-1,-1) BottomLeft for white
				$x = $fidx - 1; $y = $ridx - 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 4 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 5 (0,-1) Bottom for white
				$x = $fidx; $y = $ridx - 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 5 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 6 (1,-1) BottomRight for white
				$x = $fidx+1; $y = $ridx - 1 *$sign;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 6 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 7 (-1,0) Left for white
				$x = $fidx - 1; $y = $ridx;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 7 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				# option 8 (1,0) Right for white
				$x = $fidx + 1; $y = $ridx;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						
						# echo"\n\n 8 KING $loc ($mloc) $color :: $status \n\n";
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
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
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}

				
				# option 2 
				$x = $fidx + 2; $y = $ridx + 1;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 3 
				$x = $fidx + 1; $y = $ridx - 2;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 4 
				$x = $fidx + 2; $y = $ridx - 1;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 5 
				$x = $fidx - 1; $y = $ridx + 2;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 6 
				$x = $fidx - 2; $y = $ridx + 1;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 7 
				$x = $fidx - 1; $y = $ridx - 2;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
						
				# option 8 
				$x = $fidx - 2; $y = $ridx - 1;
				$mloc = $this->isValidLocation($x,$y);
					if($mloc)
						{ 
						$status = $this->isBlank($mloc, $color);
						if($status == "blank") { $moves[] =  $mloc; }
						if($status == "enemy") 
							{
							$moves[] =  $mloc;
							$captures[] = $mloc; 
							}
						if($status == "self")
							{
							$protects[] = $mloc;
							}
						}
				
				
				
				
				
				}
			break;
			}
		
		return array("moves"=>$moves,"captures"=>$captures,"protects"=>$protects);
		}
	
		
	public function basicAnalysis() 
		{
		$this->moveinfo["canMoveThis"] = array("w"=>array(), "b"=>array());
		
		$this->moveinfo["canMoveTo"] = array("w"=>array(), "b"=>array());
		# loop over getPossibleMoves per piece ...
		foreach($this->board["pieces"]["w"] as $piece=>$locs)
			{
			foreach($locs as $loc)
				{
				# echo"\n ################ loc: $loc ################ \n";
				$info = $this->getPossibleMoves($loc);
				$this->moveinfo["w"][$loc] = $info;
				if(sizeof($info["moves"]) > 0)
					{
					$this->moveinfo["canMoveThis"]["w"][] = $loc;
					foreach($info["moves"] as $move)
						{
						$this->moveinfo["canMoveTo"]["w"][$move][] = $loc;
						}
					}
				}
			}
		foreach($this->board["pieces"]["b"] as $piece=>$locs)
			{
			foreach($locs as $loc)
				{
				# echo"\n ################ loc: $loc ################ \n";
				
				$info = $this->getPossibleMoves($loc);
				$this->moveinfo["b"][$loc] = $info;
				if(sizeof($info["moves"]) > 0)
					{
					$this->moveinfo["canMoveThis"]["b"][] = $loc;
					foreach($info["moves"] as $move)
						{
						$this->moveinfo["canMoveTo"]["b"][$move][] = $loc;
						}
					}
				}
			}
		}
	
	public function basicScorePiece($piece)
		{
		$P = strtoupper($piece);
		switch($P)
			{
			default: // blank
				$score = -1;
			break;
			
			case "P":
				$score = 1;
			break;
			
			case "N":
			case "B":
				$score = 3;
			break;
			
			case "R":
				$score = 5;
			break;
			
			case "Q":
				$score = 9;
			break;
			
			case "K":
				$score = 0;
			break;
			}
		return $score;
		}
	
	}
	
# store game history with UCI-SAN-long notations
# history[moveNumber][w]move[san]
#							[uci]
#							[details]
#							[flag-enpassant]
#						[fen-start] + [move]	

	





?>

