<?php
class Config {
	var $values = array();

	public function Config() {
		global $database;
		$this->values = $database->db->GetAssoc("SELECT name, value FROM config");
	}
	public function save($name=null) {
		global $database;

		if(is_null($name)) {
			foreach($this->values as $name => $value) {
				// does "or update" work with sqlite / postgres?
				$database->db->StartTrans();
				$database->Execute("DELETE FROM config WHERE name = ?", array($name));
				$database->Execute("INSERT INTO config VALUES (?, ?)", array($name, $value));
				$database->db->CommitTrans();
			}
		}
		else {
			$database->db->StartTrans();
			$database->Execute("DELETE FROM config WHERE name = ?", array($name));
			$database->Execute("INSERT INTO config VALUES (?, ?)", array($name, $this->values[$name]));
			$database->db->CommitTrans();
		}
	}

	public function set_int($name, $value) {
		$this->values[$name] = parse_shorthand_int($value);
		$this->save($name);
	}
	public function set_string($name, $value) {
		$this->values[$name] = $value;
		$this->save($name);
	}
	public function set_bool($name, $value) {
		$this->values[$name] = (($value == 'on' || $value === true) ? 'Y' : 'N');
		$this->save($name);
	}

	public function set_default_int($name, $value) {
		if(is_null($this->get($name))) {
			$this->values[$name] = parse_shorthand_int($value);
		}
	}
	public function set_default_string($name, $value) {
		if(is_null($this->get($name))) {
			$this->values[$name] = $value;
		}
	}
	public function set_default_bool($name, $value) {
		if(is_null($this->get($name))) {
			$this->values[$name] = (($value == 'on' || $value === true) ? 'Y' : 'N');
		}
	}

	public function get_int($name, $default=null) {
		// deprecated -- ints should be stored as ints now
		return parse_shorthand_int($this->get($name, $default));
	}
	public function get_string($name, $default=null) {
		return $this->get($name, $default);
	}
	public function get_bool($name, $default=null) {
		// deprecated -- bools should be stored as Y/N now
		return ($this->get($name, $default) == 'Y' || $this->get($name, $default) == '1');
	}

	private function get($name, $default=null) {
		if(isset($this->values[$name])) {
			return $this->values[$name];
		}
		else {
			return $default;
		}
	}
}
?>
