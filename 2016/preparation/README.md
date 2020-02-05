# HashCode 2016 - Preparation
Read the [problem statement](painting.pdf) to understand what Google wanted you to implement it.
Below you can find some concepts that we have used.

## Rule
- the number of commands must be less than the number of cells: logo.in has 1120 cells
- the final score is the number of cells - the number of commands
- the winner is the one who scores the most points

## Commands
```
PAINT_SQUARE 2 3 1 # for printing 1 square of 9 cells centered to [2,3]
ERASE_CELL 2 3 # for not printing 1 single cell [2,3]
PAINT_LINE 0 4 3 4 # for printing 1 line from [0,4] to [3,4]
```

## Considerations
If you do everything in lines, there are 14 lines and for each line there are a commands total of 70
```
1, 2, 3, 4, 6, 11, 11, 10, 11, 6, 1, 2, 1, 1 = 70
```
If you also use the square,
- the "l" 9 line = 1 square
- the "g" 15 line = 1 line, 3 square, ..

With a matrix of [01] for finding the better path in horizontal / vertical,
- the better path = line
- if there are more better pathes in parallel = square
- it is important to evaluate when it is better to use ERASE_CELL
