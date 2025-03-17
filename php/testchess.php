<?PHP
ob_start();
echo"<PRE>";
include("class.chess.php");

# http://localhost/stockfish/testchess.php?fen=rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1


$fen = "rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1";
if(isset($_GET["fen"])) 	{ $fen = $_GET["fen"]; }


$chess = new chess($fen);
$chess->debug = true; # the response file is not deleted when "close" is called.



# called in constructor 
# $chess->setupBoard($fen); 

/*
	# for hacking, let's place the bishop at d4 
	$chess->board["rank"]["4"]["d"] = $chess->board["file"]["d"]["4"] = "B"; # black bishop on dark squares ...
	
	# for hacking, let's place the rook at d4 
	$chess->board["rank"]["4"]["d"] = $chess->board["file"]["d"]["4"] = "r"; # white rook ...
	
	# for hacking, let's place the rook at d4 
	$chess->board["rank"]["4"]["d"] = $chess->board["file"]["d"]["4"] = "q"; # white q ...

$info = $chess->getPossibleMoves("d4");

	
	# for hacking, let's place the knight at d4 
	#$chess->board["rank"]["3"]["f"] = $chess->board["file"]["f"]["3"] = "N"; # black knight ...
#$info = $chess->getPossibleMoves("f3");
	
	
print_r($info);	
*/

$chess->basicAnalysis();

/*
$piece = $chess->getPieceAtPosition("e2");
$color = $chess->getPieceColor($piece);
echo"<PRE>"; print_r($piece); print_r("\t\t\t"); print_r($color);
*/

$chess->close();

print_r($chess);
/*
$mloc = "e8";
$piece = $chess->getPieceAtPosition($mloc);
$color = $chess->getPieceColor($piece);
		
echo"\n\n mloc: $mloc \t\t piece: $piece \t color: $color \n\n";
*/
	
echo"</PRE>";exit;

/*
On a 12+ year old laptop [64-bit, 8GB RAM, Intel(R) Core(TM) i7-4800MQ CPU @ 2.70GHz 2.70 GHz], running stockfish [stockfish-windows-x86-64-sse41-popcnt 03/15/2025], the system takes between 0.5-1.0 seconds to complete either request with fen=startposition.  Will it be faster with a faster processor?
*/

/*
 piece: K	 color: b		 file: e	 rank: 8			 loc: e8 		 prev: 



 mloc: d7 		 who: w 		 piece: P 		 color: b 

*/ # how is the color changing ... color is "blank"???



ob_end_clean();
header('Content-type: text/javascript');
echo(json_encode($stockfish->payload, JSON_PRETTY_PRINT));exit;
?>