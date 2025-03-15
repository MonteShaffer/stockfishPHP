<?PHP
echo"<PRE>";
include("./../class.stockfish.php");
$stockfish = new stockfish();
$stockfish->debug = true; # the response file is not deleted when "close" is called.

$stockfish->sendCommand();

$stockfish->close();

print_r($stockfish);
echo"</PRE>";exit;
?>