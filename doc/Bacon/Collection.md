A hybrid between a collection and entity
=========================


        require_once('../../lib/Bacon/Collection.php');
                
        class Dude extends \Bacon\Collection {
        
          public function getName() {
        		return $this['fname'].' '.$this['lname'];
        	}
        
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
        
        $data = array(
        	array('id' => 5, 'fname' => 'tom', 'lname' => 'jones'),
        	array('id' => 6, 'fname' => 'Joe', 'lname' => 'Shmoe'),
        );
        
        $col = new Dude($data);
        foreach($col as $d) {
        	echo $d->getName()."\n";
        }
        
        $dude = new Dude($data[0]);
        echo $dude->getName();