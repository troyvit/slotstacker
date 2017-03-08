<?php

class robot {
	public $slots;
    public $slot_state = array();
    public $command_list = array();
	// $slots = array($blocks, $blocks, $blocks, $blocks);
	public $allowed_commands=array(
		'size' => array('type' => 'int', 'num_params' => 1),
		'add'  => array('type' => 'int', 'num_params' => 1),
		'mv'   => array('type' => 'int', 'num_params' => 2),
		'rm'   => array('type' => 'int', 'num_params' => 1),
		'undo'   => array('type' => 'int', 'num_params' => 1),
		'replay'   => array('type' => 'int', 'num_params' => 1),
	    );

    function show_slots() {
        $slots = $this -> slots;
        foreach($slots as $slot => $blocks) {
            $block_stack = '';
            $space="";
            $i=1;
            while($i <= $blocks) {
                $i++;
                $block_stack.=$space.'X';
                $space=" ";
            }
            echo "$slot: $block_stack\n";
        }
        echo "";
    }

	function make_slots($size) {
        $i=1;
        while($i <= $size) {
            $slots[$i]=0;
            $i++;
        }
        $this -> slots = $slots;
	}

	function add_block($number) {
        $this ->slots[$number]++;
        return true;
	}

	function remove_block ($number) {
        $this ->slots[$number]--;
        return true;
	}

	function move_block ($from, $to) {
        $this ->slots[$to]++;
        $this ->slots[$from]--;
	}

	function replay($number_commands) {
        $cl = $this -> command_list;
        $c = count($cl);
        $cl_start = $c - $number_commands;
        echo "We robots have run the following commands: \n";
        while($cl_start < $c) {
            echo $cl[$cl_start];
            echo "\n";
            $cl_start++;
        }
        $cl_start = $c - $number_commands;
        while($cl_start < $c) {
            $this -> run ($cl[$cl_start], false);
            $cl_start++;
        }
	}

	function undo($num_undoes) {

        $slot_state = $this -> slot_state;
        $slot_count = count($slot_state);
        $new_slot_state = $slot_count - $num_undoes;
        $this -> slots = $slot_state[$new_slot_state];
        $i=0;
        while($i < $num_undoes) {
            $i++;
            $latest_slot = array_pop($this -> slot_state);
        }
	}

    function test_param($command, $param) {
        $type = $this -> allowed_commands[$command]['type'];
        if($type == 'int') {
            $ret = (int)$param; 
            return $ret;
        } // expand for other types of params
    }

	function run($command, $show_slots = true) {
        if($command == 'exit') {
            return true;
        }
        $slots = $this -> slots;
        $ac = $this -> allowed_commands;
        // make sure the command doesn't have extra spaces since we use those spaces later
        $command = preg_replace('/\s\s+/', ' ', $command);
        $command_arr = explode(' ', $command);
        
        // pull the commmand off the front of the line and keep the params
        $command_name = array_shift($command_arr);

        // error check: is the input command one we support?
        if(@!is_array($ac[$command_name])) {
            $error = "Sorry $command_name is not supported \n";
            $ret = array('error' => $error);
            return $ret;
        }
        
        $type       = $ac[$command_name]['type'];
        $num_params = $ac[$command_name]['num_params'];

        // separate the command parameters from the entered text, assuming the command comes first and the params represent everything after

        // error check: did they include all the params they need?
        $cp_count = count($command_arr);
        if($cp_count > $num_params || $cp_count < $num_params) {
            $error = "Did you include the wrong number of parameters when you called $command_name? There should be $num_params but you called $cp_count. ($command)";
            $ret = array('error' => $error);
            return $ret;
        }

        // error check: is each param the correct data type?
        foreach($command_arr as $param) {
            $tested_param = $this -> test_param($command_name, $param);
            if($tested_param == false) {
                $error = "The parameter $param is not valid for $command_name. ($command)";
                $ret = array('error' => $error);
                return $ret;
                die();
            }
        }

        // still here eh? let's actually run the command then
        // I wrote a command interpreter for a fairly complex API that leveraged the API's grammar to execute its commands. It was a lot of backend work that took many hours, so for this I'll keep it simple (and not as extensible).

        if($command_name == 'size') {
            // not much error checking here. We know it's a number by now
            $size = $command_arr[0];
            $slot_count = count($slots);
            $this -> make_slots($size);
        }

        if($command_name == 'add' || $command_name == 'rm') {
            $to = $command_arr[0];
            if(@!$slots[$to] && @$slots[$to] !==0) {
                $error = "We don't find a slot in position $to. Do you need to add a slot? If so try size $to to ensure you have enough slots. ";
                $ret = array('error' => $error);
                return $ret;
            }

            if($command_name == 'add') {
                $this -> add_block($to);
            }
            if($command_name == 'rm') {
                $this -> remove_block($to);
            }
        }

        if($command_name == 'mv') {
            $from = $command_arr[0];
            $to = $command_arr[1];
            if($slots[$from] == 0) {
                $error = "No blocks in slot $from! Please add some blocks to that slot before moving them.";
                $ret = array('error' => $error);
                return $ret;
            }

            if(!$slots[$to] && $slots[$to] !== 0) {
                $error =  "It looks like we don't have enough slots to move a block to slot $to. Perhaps you should add one. Typing size $to might do it. ($command)";
                $ret = array('error' => $error);
                return $ret;
            }
            // all is good
            $this -> move_block($from, $to);
        }


        if($command_name == 'replay') {
            $number_commands = (int)$command_arr[0];
            $cl = $this -> command_list;
            $c = count($cl);
            if($number_commands > $c) {
                $error =  "History only goes back $c steps so far, but the request was for $number_commands. Do some more stuff and then try again. ($command)";
                $ret = array('error' => $error);
                return $ret;
            }

            $this -> replay($number_commands);
        }

        if($command_name == 'undo') {
            $slot_state = $this -> slot_state;
            $slot_count = count($slot_state);
            $num_undoes = $command_arr[0];

            if($num_undoes > $slot_count) {
                $error =  "You've only done $c things so far, but the request was for $num_undoes undoes. In other words you haven't made enough mistakes yet to feel this level of regret. ($command)";
                $ret = array('error' => $error);
                return $ret;
            }

            $this -> undo($num_undoes);
        }

        if($show_slots == true) {
            $this -> show_slots();
        }
        if(strpos($command, 'replay') === false && strpos($command, 'undo') === false) {
            array_push($this -> slot_state, $slots);
            array_push($this -> command_list, $command);
        }
	}
}

$r = new robot;
$command = '';
$error = '';
$attempt_count = 0;

// echo "$about\n> ";

$first_run = true;

while($command !=='exit') {
    $num_slots = count($r -> slots);
    if($num_slots == 0) {
        if($first_run !== true) {
            $msg = "Error. Please start by adding some slots with the 'size' command\n";
        } else {
            $msg = "Start by adding some slots with the 'size' command\n";
        }
    } else {
        $msg = '';
    }
    echo $msg;
    $first_run = false;
    fwrite(STDOUT, "\n> ");
    $command = trim(fgets(STDIN));
    $ret = $r -> run($command);
    if(strlen($ret['error']) > 0) {
        echo $ret['error'];
    }
    unset($ret);
    if($command == 'exit') {
        echo "Bye!\n";
        die();
    }
}

echo "\nBye!\n";
die();
