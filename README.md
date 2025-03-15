# stockfishPHP
API for stockfish

This PHP class allows you to interface with the stockfish executable on your local device (accessible through localhost).

The class DOES NOT rely on `stream_get_contents` as it can cause issues on Windoze with blocking issues.

The examples folder contains different use cases.  If installed on Windoze, I would recommend XAMPP to allow for the interface.

This API is used as part of the `chess-mates` interface

## Examples 
### Start UCI 
```
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
```
### Evaluate Position 
```
<?PHP
echo"<PRE>";
include("./../class.stockfish.php");
$stockfish = new stockfish();
$stockfish->debug = true; # the response file is not deleted when "close" is called.

$stockfish->evalPosition();

$stockfish->close();

print_r($stockfish);
echo"</PRE>";exit;
?>
```
### Find Best Move 
```
<?PHP
echo"<PRE>";
include("./../class.stockfish.php");
$stockfish = new stockfish();
$stockfish->debug = true; # the response file is not deleted when "close" is called.

$stockfish->findBestMove();

$stockfish->close();

print_r($stockfish);
echo"</PRE>";exit;
?>
```
