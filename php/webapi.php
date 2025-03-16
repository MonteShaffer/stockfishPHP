<?PHP
ob_start();
#echo"<PRE>";
include("class.stockfish.php");
$stockfish = new stockfish();
#$stockfish->debug = true; # the response file is not deleted when "close" is called.

# http://localhost/stockfish/webapi.php?action=bestmove&elo=1500&method=depth 10&fen=rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1

# http://localhost/stockfish/webapi.php?action=eval&fen=rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1

$fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
if(isset($_GET["fen"])) 	{ $fen = $_GET["fen"]; }
$method = "depth 15";
if(isset($_GET["method"])) 	{ $method = $_GET["method"]; }
$elo = 0; # this (zero) will use maximum ELO for stockfish 3000+
if(isset($_GET["elo"])) 	{ $elo = $_GET["elo"]; }
$action = "bestmove";
if(isset($_GET["action"])) 	{ $action = $_GET["action"]; }

switch($action)
	{
	default:
	case "bestmove":
		$stockfish->findBestMove($fen, $method, $elo); 
	break;
	
	case "eval":
		$stockfish->evalPosition($fen);
	break;
	}

$stockfish->close();

#print_r($stockfish);
#echo"</PRE>";exit;

/*
On a 12+ year old laptop [64-bit, 8GB RAM, Intel(R) Core(TM) i7-4800MQ CPU @ 2.70GHz 2.70 GHz], running stockfish [stockfish-windows-x86-64-sse41-popcnt 03/15/2025], the system takes between 0.5-1.0 seconds to complete either request with fen=startposition.  Will it be faster with a faster processor?
*/




ob_end_clean();
header('Content-type: text/javascript');
echo(json_encode($stockfish->payload, JSON_PRETTY_PRINT));exit;
?>