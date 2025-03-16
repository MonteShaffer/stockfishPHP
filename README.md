# stockfishPHP
API for stockfish

This PHP class allows you to interface with the stockfish executable on your local device (accessible through localhost).

The class DOES NOT rely on `stream_get_contents` as it can cause issues on Windoze with blocking issues.

The examples folder contains different use cases.  If installed on Windoze, I would recommend XAMPP to allow for the interface.

### httpd.conf for XAMPP, add Alias
```
Alias /stockfish "C:/_git_/github/MonteShaffer/stockfishPHP/php"
<Directory "C:/_git_/github/MonteShaffer/stockfishPHP/php">
    AllowOverride All
    Require all granted
    Options Indexes
</Directory>
```
If properly configured, `http://localhost/stockfish/webapi.php?action=bestmove&elo=1500&method=depth 10&fen=rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1` will provide the best move as-if stockfish has an ELO of 1500.

If properly configured, `http://localhost/stockfish/webapi.php?action=eval&fen=rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1` will evaluate the current position.

#### NOTE: System 
On a 12+ year old laptop [64-bit, 8GB RAM, Intel(R) Core(TM) i7-4800MQ CPU @ 2.70GHz 2.70 GHz], running stockfish [stockfish-windows-x86-64-sse41-popcnt 03/15/2025], the system takes between 0.5-1.0 seconds to complete either request with fen=startposition.  Will it be faster with a faster processor?


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

### Find Best Move (all options: 1500 ELO)
```
<?PHP
echo"<PRE>";
include("./../class.stockfish.php");
$stockfish = new stockfish();
$stockfish->debug = true; # the response file is not deleted when "close" is called.

$stockfish->findBestMove("rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1", "depth 15", 1500); # anchored to ELO of 1500

$stockfish->close();

print_r($stockfish);
echo"</PRE>";exit;
?>
```