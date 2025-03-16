<?PHP

class stockfish
	{
	private $process;
	private $pipes;
	private $offset;
	public $out;
	
	public function __construct($EXE = "C:/stockfish/stockfish.exe")
		{		
		$this->EXE		= $EXE;
		$this->id = uniqid('', true);
		$this-> STDOUT = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".stdout" . DIRECTORY_SEPARATOR . 'stdout-' . $this->id . ".txt";
		touch($this->STDOUT); # empty
		/* standard streaming of pipes has blocking issues in Windoze
		Best to try and grab STDOUT from a file
		# https://www.php.net/manual/en/function.stream-get-contents.php
		# stream_set_blocking($this->pipes[1], 0);
		stream_get_contents would be nice to get latest from stack, but
		ALAS, it is waiting forever for the amount of content to be 
		delivered
		*/		
		$descr = array	(
						0 => array("pipe", "r"), 	#iStream as stdin
						1 => array("file", $this-> STDOUT, "w"),	#oStream as stdout
						2 => array("pipe", "w")		#eStream as stderr
						);
						
		$this->pipes 	= array();
		$this->out 		= array();
		$this->stream	= "";
		$this->process 	= proc_open($this->EXE, $descr, $this->pipes);
		
		$this->timeout 	= 5; # isComplete timeout (in seconds)
		$this->start 	= microtime(true);
		$this->now		= microtime(true);
		$this->micro	= 10000; # 1/100th of a second, time to wait for response 
		
		
		$this->payload 	= array();
		$this->debug 	= false;
		}
	public function sendCommand($cmd="uci")
		{
		$this->now = microtime(true);
		# if (is_resource($this->process)) {
		$this->out[$this->now.""]["cmd"] = $cmd; # log activity
		
		fwrite($this->pipes[0], $cmd."\n");
		fflush($this->pipes[0]);
		usleep($this->micro);
		$this->getResponse();
		# https://official-stockfish.github.io/docs/stockfish-wiki/UCI-&-Commands.html
		# position startpos
		# position startpos moves e2e4 e7e5 g1f3
		# position fen 8/1B6/8/5p2/8/8/5Qrq/1K1R2bk w - - 0 1
		# position fen 8/3P3k/n2K3p/2p3n1/1b4N1/2p1p1P1/8/3B4 w - - 0 1 moves g4f6 h7g7 f6h5 g7g6 d1c2
			# rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1
			# rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1
			# rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2
			# rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2
		# isready
		# d 
		# eval 					# NNUE and Final
		# setoption name <id> [value <x>] 
		# setoption name UCI_LimitStrength value true 
		# setoption name UCI_Elo value 1500  # min is 1320, max is 3190 
		# setoption name Skill Level value 5 # min is 0, max is 20?
		# go depth 15 
		# go movetime 5000
		}
		
	public function getResponse()
		{
		$this->out[$this->now.""]["response"] = $this->stream = file_get_contents($this-> STDOUT);		
		}
	
	public function isComplete($what="bestmove")
		{
		$this->now = microtime(true); 
		$this->time = $this->now - $this->start;
		if($this->time > $this->timeout) { return false; }
		# isready returns "ok" even when search is still ongoing
		while (strpos($this->stream, $what) === false) 
			{
			# set a delay before checking again
			usleep($this->micro);
			$this->getResponse();
			}
		return true;
		}


	public function setOption($key="UCI_LimitStrength",$value="true")
		{
		$this->payload["option"][$key] = $value;
		$cmd = "setoption name {option} value {value}";
		$cmd = str_replace("{option}", $key, $cmd);
		$cmd = str_replace("{value}",  $value,  $cmd);
		$this->sendCommand($cmd);		
		}
	
	#public evalPosition = function($fen ="rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
	public function evalPosition ($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1")
		{
		$this->sendCommand("uci");
		$this->sendCommand("ucinewgame");
		$this->sendCommand("position fen ".$fen);
		$this->sendCommand("eval");
		$this->isComplete("Final evaluation");
		$this->payload["eval"] = $this->parseEval();
		
		
		}
	
	public function findBestMove ($fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1", $method = "depth 10", $elo = 0)
		{
		$this->sendCommand("uci");
		$this->sendCommand("ucinewgame");
		if($elo > 0)
			{
			if($elo < 1320) { $elo = 1500; } # minimum 
			if($elo < 3000) # this is approaching maximum
				{
				$this->setOption("UCI_LimitStrength","true");
				$this->setOption("UCI_Elo",$elo);
				}
			}
		
		$this->sendCommand("position fen ".$fen);
		$this->sendCommand("go ".$method);
		$this->isComplete("bestmove");
		$this->payload["bestmove"] = $this->parseBestMove();
		
		} 
	
	public function parseBestMove($str="")
		{		
		if(empty($str)) { $str = $this->stream; }
		$data = array();
		$data["bestmove"] = "";
		
			$tmp = explode("info depth", $str);
			$len = sizeof($tmp);
		$last = $tmp[$len - 1];
			$tmp2 = explode("ponder",$last);
		$data["ponder"] = (isset($tmp2[1])) ? trim($tmp2[1]): "";
			$tmp2 = explode("bestmove",$last);
			$tmp3 = explode(" ",$tmp2[1]);
		$data["bestmove"] = $tmp3[1];
			$tmp3 = explode(" ",$tmp2[0]);
		$data["details"] = array();
			$data["details"]["depth"] = $tmp3[1];
			$data["details"]["seldepth"] = $tmp3[3];
			$data["details"]["multipv"] = $tmp3[5];
			$data["details"]["cp"] = $tmp3[8]; # centipawns (eval)
			$data["details"]["nodes"] = $tmp3[10];
			$data["details"]["nps"] = $tmp3[12];
			$data["details"]["moves"] = implode(" ", array_slice($tmp3,20,1+sizeof($tmp3)-20));
			
		
		
		return $data;
		}
	public function parseEval($str="")
		{
		if(empty($str)) { $str = $this->stream; }
		$data = array();
			$tmp = explode("NNUE network contributions", $str);
			$tmp2 = explode("NNUE evaluation", $tmp[1]);
			$tmp3 = explode("(",$tmp2[1]);
		$nnue = trim($tmp3[0]);
			$tmp = explode("Final evaluation", $this->stream);
			$tmp2 = explode("(",$tmp[1]);
		$final = trim($tmp2[0]);
		
		$data = array("NNUE"=>$nnue,"Final"=>$final);
		return $data;
		}

	public function close()
		{
		fclose($this->pipes[0]);
		# file is not a pointer to a resource 
		# fclose($this->pipes[1]);
		if(!$this->debug) { @unlink($this->STDOUT); }
		fclose($this->pipes[2]);		
		
		proc_close($this->process);
		
		$this->now = microtime(true); 
		$this->time = $this->now - $this->start;
		$this->payload["id"] = $this->id;
		$this->payload["time"] = $this->time;
		}
	
	}

?>