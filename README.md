# slotstacker
This is a robot designed to replace humans who endeavor to place boxes into slots. They will be dealt with gently (the boxes, not the humans).

The robot starts by allowing the user to select the number of slots required to place boxes. Once the user has created slots there are several choices. Once may:

* Add a box to a designated slot
* Remove a box from a designated slot
* Move a box from one slot to another
* Undo any add, Remove, or Move in the user's history
* Replay any add, Remove, or Move in the user's history

Below are full instructions.

# instructions
To start the script type:

`./human_replacement.php`

from within the repository folder. If it is executable and can find PHP on your system it will run. If the file isn't executable you can type:

`php human_replacement.php`

Once you're in here's what you can do:

+ Create a new set of slots by typing:

   size [n]

   where [n] is the number of slots you want.

+ Add a box to a slot by typing:

   add [n]

   where [n] is the number of the slot you would like to add the box to.

+ Remove a box from a slot by typing:

   rm [n]

   where [n] is the number of the slot from which you would like to remove the box.

+ Move a box from one slot to another by typing:

   mv [n] [x]

   where [n] is the slot from which you move a box and [x] is the destination.

+ Undo movements by typing:

   undo [n]

   where [n] is how far back in history you would like to go with an undo. For instance if you type:

   add 1
   add 2
   add 3
   undo 2

   the robot will remove one box from slot 2 and one box from slot 3.

+ replay a past movement by typing:

   replay [n]

   where [n] is how far back in history you would like to go to make a replay. 

   For instance if you type:

   add 1
   add 2
   add 3
   replay 2

   the robot will add a new box to slot 2 and a new box to slot 3.

   NOTE: undo and replay are not stored in history, so you cannot undo an undo, or replay a replay.

+ Exit the program by typing:

   exit
