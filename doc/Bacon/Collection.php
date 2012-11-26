<?php
require_once('../../lib/Bacon/Collection.php');

/**
 * This example tries to illustrate advantage of collection on a very simple example.
 * A typical class "Dude" is created that extends Collection.
 *
 * @author tomasz
 *
 */

class Dude extends \Bacon\Collection {

	public function getName() {
		return $this['fname'].' '.$this['lname'];
	}

	/*
	 * A basic offsetGet is overwriten to handle transformation of fname and lname fields.
	 */
	public function offsetGet($offset) {
		switch($offset) {
			case 'fname':
			case 'lname':
				return ucfirst(parent::offsetGet($offset));
			default:
				return parent::offsetGet($offset);

		}
	}

}

/*
 * Sample data set. This could come from DB.
 */
$data = array(
	array('id' => 5, 'fname' => 'tom', 'lname' => 'jones'),
	array('id' => 6, 'fname' => 'Joe', 'lname' => 'Shmoe'),
);

/*
 * Instantiating Dude with the whole recordset. Now "dude" like a collection.
 * Only one "Dude" object is created. On each iteration, a new "row" is referenced by all other functions
 */
$col = new Dude($data);
foreach($col as $d) {
	echo $d->getName()."\n";
}

/*
 * Here we're just setting one record. Useful for retriving a single row from DB, forexample for editing
 */
$dude = new Dude($data[0]);
echo $dude->getName();