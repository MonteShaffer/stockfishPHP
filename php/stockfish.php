<?PHP
echo"<PRE>";
include("class.stockfish.php");
$stockfish = new stockfish();
#$stockfish->debug = true;

#$stockfish->sendCommand();
#$stockfish->evalPosition();
$stockfish->findBestMove();

$stockfish->close();

print_r($stockfish);

echo"</PRE>";exit;

?>