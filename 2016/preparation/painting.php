<?php

$lines = file('./logo.in');
list($rows, $cols) = explode(" ", $lines[0]);
unset($lines[0]);
$m = array();

foreach($lines as $line){
 $line = trim($line);
 $m[] = str_split($line);
}

// how many horizontal lines?
$hlines = array();
$hline = 0;
$hstart = 0;
for($i=0;$i<$rows;$i++){
 for($j=0;$j<$cols;$j++){
  if($m[$i][$j]!='.'){
   if($hline==0){
    $hline++;
    $hstart=$j;
   }
   if($j+1<$cols && $m[$i][$j+1]!='.'){
    if($m[$i][$j]=='v'){
     $m[$i][$j] = 'x';
    } else {
     $m[$i][$j] = 'o';
    }
   } elseif($m[$i][$j]=='#') {
    $m[$i][$j] = 'o';
   }
   if($i+1<$rows && $m[$i+1][$j]!='.'){
    if($m[$i][$j]=='o'){
     $m[$i][$j] = 'x';
    } else {
     $m[$i][$j] = 'v';
    }
    $m[$i+1][$j] = 'v';
   }
   // else {
   //  $m[$i][$j] = 'o';
   // }
  } else {
   list($hline, $hstart, $hlines) = closeLine($i, $j, $hline, $hstart, $hlines);
  }
 }
 list($hline, $hstart, $hlines) = closeLine($i, $j, $hline, $hstart, $hlines);
}

// how many vertical lines?
$vlines = array();
$vline = 0;
$vstart = 0;
for($i=0;$i<$cols;$i++){
 for($j=0;$j<$rows;$j++){
  if($m[$j][$i]!='.'){
   if($vline==0){
    $vline++;
    $vstart=$j;
   }
  } else {
   list($vline, $vstart, $vlines) = closeReverseLine($j, $i, $vline, $vstart, $vlines);
  }
 }
 list($vline, $vstart, $vlines) = closeReverseLine($j, $i, $vline, $vstart, $vlines);
}

// how many squares?
$squares = array();
// how many commands?
$commands = array();
for($i=0;$i<count($hlines);$i++){
 list($r, $s, $r, $e) = $hlines[$i];
 // if end - start > 1 and there are x > 1
 $possibleSquares = whichStarts($r,$s,$e,$m);
 // @todo: > 1 per logo.in che non ha 1 erase per 1 square
 if($e - $s > 1 && count($possibleSquares) > 0){
  $nextRow = $r + 1;
  for($j=0;$j<count($hlines);$j++){
   list($r2, $s2, $r2, $e2) = $hlines[$j];
   // if row is next and there are x > 1
   if($r2 == $nextRow){
    $possibleSquares2 = whichStarts($r2,$s2,$e2,$m);
    // @todo: > 1 per logo.in che non ha 1 erase per 1 square
    if($e2 - $s2 > 1 && count($possibleSquares2) > 0){
     $md5 = md5(serialize($possibleSquares));
     $possibleSquares = updatePossibleSquares($possibleSquares, $possibleSquares2);
     if (count($possibleSquares) > 0 && $md5 != md5(serialize($possibleSquares))){ 
      // $j++ for next row
      $nextRow++;
     } else {
      // if too small
      //$j = count($hlines);
     }
    }
   } elseif($r2>$nextRow){
    $j = count($hlines);
   }
  }
  // close square
  $squares = closeSquares($squares, $possibleSquares);
 }
}

foreach($squares as $square){
 list($r1, $c1, $r2, $c2) = $square;
 $towards = 's';
 for($i=$c1;$i<=$c2;$i++){
  for($j=$r1;$j<=$r2;$j++){
   $m[$j][$i] = $towards;
  }
 }
}

// how many commands?
$finalCommands = $squares;
$lines = count($hlines) <= count($vlines) ? $hlines : $vlines;
foreach($lines as $command){
 list($r1, $c1, $r2, $c2) = $command;
 if(plusArea($r1, $c1, $r2, $c2)){
  $finalCommands[] = $command;
  $towards = 'o';
  if($r2-$r1>$c2-$c1){
   $towards = 'v';
  }
  for($i=$c1;$i<=$c2;$i++){
   for($j=$r1;$j<=$r2;$j++){
    $m[$j][$i] = $towards;
   }
  }
 }
}

// sign erase cells / delete square / delete hlines / delete vlines
echo "How many horizontal lines? " . count($hlines) . "\n";
echo "How many vertical lines? " . count($vlines) . "\n";
echo "How many squares? " . count($squares) . "\n";
echo "How many commands? " . count($finalCommands) . "\n";
echo printMatrix($m);

foreach($finalCommands as $command){
 list($r1, $c1, $r2, $c2) = $command;
 if($r1!=$r2 && $c1!=$c2){
  list($r, $c, $l) = getCenter($r1, $c1, $r2, $c2);
  echo "PAINT_SQUARE $r $c $l\n";
 } else {
  echo "PAINT_LINE $r1 $c1 $r2 $c2\n";
 }
}

function getCenter($r1, $c1, $r2, $c2){
 echo "$r1, $c1, $r2, $c2\n";
 $r = ($r2 - $r1) / 2;
 $c = ($c2 - $c1) / 2;
 $l = ($r2 - $r + 1) / 2;
 return array($r, $c, $l);
}

function printMatrix($m){
 foreach($m as $row){
  foreach($row as $cell){
   echo $cell;
  }
  echo "\n";
 }
}

function closeLine($i, $j, $line, $start, $lines){
 if($line==1){
  $line=0;
  $lines[] = array($i, $start, $i, $j-1);
  $start=0;
 }
 return array($line, $start, $lines);
}

function closeReverseLine($i, $j, $line, $start, $lines){
 if($line==1){
  $line=0;
  $lines[] = array($j, $start, $j, $i-1);
  $start=0;
 }
 return array($line, $start, $lines);
}

function whichStarts($r, $s, $e, $m){
 $starts = array();
 // get starts by x
 $xSuccessive = 0;
 $xPrevious = 0;
 $xStart = 0;
 for($i=$s;$i<=$e;$i++){
  if($m[$r][$i]=='x' || $m[$r][$i]=='v'){
   if(($xPrevious==0 && $xSuccessive==0) || ($i>0 && $xPrevious==$i-1)){
    $xSuccessive++;
   } else {
    $xSuccessive = 1;
   }
   $xPrevious = $i;
   if($xStart==0 && $s!=0){
    $xStart = $i;
   }
  } else {
   // if all x = one start
   if($xSuccessive>1){
    $starts[] = array($r, $xStart, $r, $i-1);
    $xSuccessive = 0;
    $xPrevious = 0;
    $xStart = 0;
   }
  }
 }

 if($xSuccessive>1){
    $starts[] = array($r, $xStart, $r, $i-1);
    $xSuccessive = 0;
    $xPrevious = 0;
    $xStart = 0;
   }

 return $starts;
}

function updatePossibleSquares($possibleSquares, $possibleSquares2){
 // check corrispondenza tra array
 for($i=0;$i<count($possibleSquares);$i++){
  list($r1, $c1, $r2, $c2) = $possibleSquares[$i];
  for($j=0;$j<count($possibleSquares2);$j++){
   list($pr1, $pc1, $pr2, $pc2) = $possibleSquares2[$j];
   if((($c1 < $pc2 && $pc2 <= $c2) || ($c1 <= $pc1 && $pc1 < $c2) || ($pc1 <= $c1 && $c2 <= $pc2))
    && (($r1 <= $pr2 && $pr2 <= $r2 + 1) || ($r1 <= $pr1 && $pr1 <= $r2 + 1) || ($pr1 <= $r1 + 1 && $r2 <= $pr2 + 1))
   ){
    $column1 = $c1;
    $column2 = $c2;
    if($c2>$pc2){
     $column2 = $pc2;
    }
    if($c1<$pc1){
     $column1 = $pc1;
    }
    // l'area del quadrato nuovo > di quella vecchia
    if(($pr2-$r1+1)*($column2-$column1+1) > ($r2-$r1+1)*($c2-$c1+1)){
     // aggiornamento possibleSquares
     $possibleSquares[$i] = array($r1, $column1, $pr2, $column2);
    }
   }
  }
 }
 return $possibleSquares;
}

function  closeSquares($squares, $possibleSquares){
 global $hlines, $vlines;
 // @todo: potrebbe essere interessante valutare solo alcuni square e non tutti, tipo: non sovrapposti se non in determinate condizioni
 foreach($possibleSquares as $square){
  list($r1, $c1, $r2, $c2) = $square;
  // if square > 1 in vertical
  // @todo: > 1 per logo.in che non ha square interessanti per dimensioni 2x2
  if($r2-$r1 > 1 && $c2-$c1 > 1){
   $notExists = true;
   foreach($squares as $square){
    list($sr1, $sc1, $sr2, $sc2) = $square;
    // if exists square > new
    if($sr1<$r1 && $r1<$sr2 && $sr2<=$r2 && $sc1<=$c1 && $c1<$sc2 && $sc2<=$c2){
     $notExists = false;
    }
   }
   if($notExists==true){
    // @todo: se i lati non sono uguali
    // calcolare quanti quadrati servono
    // se $r2-$r1>$c2-$c1, le linee verticali sono minori
    // calcolare quante sarebbero le vlines
    // se sono minori o uguale dei quadrati, non salvare i quadrati

    // salvare cella in alto a sinistra e in basso a destra
    $squares[] = array($r1, $c1, $r2, $c2);
   }
  }
  // aggiungiamo le linee giuste ai comandi da fare
  if($r2-$r1>$c2-$c1){
   // load vlines
   loadLines($r1, $c1, $r2, $c2, $vlines);
  } else {
   // load hlines
   loadLines($r1, $c1, $r2, $c2, $hlines);
  }
 }
 return $squares;
}

function loadLines($r1, $c1, $r2, $c2, $lines){
 global $commands;
 if(($r2-$r1>$c2-$c1 && $c2-$c1>0) || ($r2-$r1<$c2-$c1 && $r2-$r1>0)){
  foreach($lines as $line){
   list($lr1, $lc1, $lr2, $lc2) = $line;
   if((($lr1<=$r2 && $lr2>=$r1) || ($lr1<=$r1 && $lr2>=$r2)) && (($lc1<=$c2 && $lc2>=$c1) || ($lc1<=$c1 && $lc2>=$c2))){
    $notExists = true;
    foreach($commands as $command){
     if($command == $line){
      $notExists = false;
     }
    }
    if($notExists==true){
     $commands[] = $line;
    }
   }
  }
 }
}

function plusArea($r1, $c1, $r2, $c2){
 global $finalCommands;
 $plusArea = true;
 foreach($finalCommands as $command){
  list($cr1, $cc1, $cr2, $cc2) = $command;
  if(((($c1>=$cc1 && $c1<=$cc2) || ($c2>=$cc1 && $c2<=$cc2)) && (($r1>=$cr1 && $r1<=$cr2) || ($r2>=$cr1 && $r2<=$cr2)))
   && $c2-$c1<=$cc2-$cc1 && $r2-$r1<=$cr2-$cr2
  ){ // @todo: || sono sfasati, ma non in questo caso
   // se la linea copre un'area uguale o minore
   $plusArea = false;
  }
 }
 return $plusArea;
}
