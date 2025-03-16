<?PHP
echo"<PRE>";
include("./../class.stockfish.php");
$stockfish = new stockfish();
$stockfish->debug = true; # the response file is not deleted when "close" is called.

$stockfish->findBestMove("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1", "depth 15", 1500); # anchored to ELO of 1500, creates more stochastic choices

$stockfish->close();

print_r($stockfish);
echo"</PRE>";exit;
?>