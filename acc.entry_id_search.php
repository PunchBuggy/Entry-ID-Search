<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Entry ID Search Accessory
 *
 * @package Entry ID Search
 * @author Punch Buggy Digital Agency
 */
class entry_id_search_acc {
	var $name			= 'Entry ID Search';
	var $id				= 'entry_id_search_acc';
	var $version		= '1.0';
	var $description	= 'Allows the searching of entries by the entry_id value.';
	var $sections		= array();
	/**
	 * Constructor
	 */
	public function entry_id_search_acc() {
		$this->EE =& get_instance();
	}


	public function set_sections() {
		$this->sections['Search For Entry ID']  = $this->search_box();
		$this->sections['Results']  = $this->results();
	}
	
	function search_box() {
		$action = $_SERVER['REQUEST_URI'];
		$action = preg_replace("/&eid.*/", "", $action);
		$session = $_GET['S'];
		$cid = (isset($_GET['cid'])) ? $_GET['cid'] : "";
		$channel_id = "";
		$dropdown = "";
		$query = $this->EE->db->query("SELECT exp_members.group_id FROM exp_members, exp_sessions WHERE exp_sessions.session_id='$session' AND exp_sessions.member_id = exp_members.member_id");
		$group_id = $query->row('group_id');
		if ($group_id == 1) {
			$query = $this->EE->db->query("SELECT channel_id, channel_title FROM exp_channels ORDER BY channel_title asc");
			$dropdown .= "<select name=\"channel_id\">";
			foreach ($query->result_array() as $row) {
				$dropdown .= '<option value="';
				$dropdown .= $row['channel_id'];
				if ($cid == $row['channel_id']) {
					$dropdown.= '" selected="selected';
				}
				$dropdown .= '">';
				$dropdown .= $row['channel_title'];
				$dropdown .= '</option>';
			}
			$dropdown .= "</select>";
		}
		else {
			$query = $this->EE->db->query("SELECT channel_id FROM exp_channel_member_groups WHERE group_id = '7'");
			foreach ($query->result_array() as $row) {
				$channel_id .= "channel_id = '" . $row['channel_id'] . "' OR ";
			}
			$channel_id = preg_replace("/OR $/", "", $channel_id);
			$query = $this->EE->db->query("SELECT channel_id, channel_title FROM exp_channels WHERE $channel_id ORDER BY channel_title asc");
			$dropdown .= "<select name=\"channel_id\">";
			foreach ($query->result_array() as $row) {
				$dropdown .= '<option value="';
				$dropdown .= $row['channel_id'];
				if ($cid == $row['channel_id']) {
					$dropdown.= '" selected="selected';
				}
				$dropdown .= '">';
				$dropdown .= $row['channel_title'];
				$dropdown .= '</option>';
			}
			$dropdown .= "</select>";
		}
		return "
			<script type='text/javascript'>
			$(document).ready(function() {
				$('#search_eid input[type=submit]').click(function(e) {
					e.preventDefault();
					document.location = '$action' + '&eid=' + $('#search_eid input[name=entry_id]').val() + '&cid=' + $('#search_eid select[name=channel_id]').val();
				});
			});
			</script>
			<form action='$action' method='post' id='search_eid'>
				<label>Entry ID <input type='text' name='entry_id' size='8' style='width: 50px; height: 21px; padding: 2px 4px; vertical-align: bottom;' /></label>
				$dropdown
				<input type='submit' value='Search' style='height: 22px;' />
			</form>
		";
	}
	
	function results() {
		$session = $_GET['S'];
		$cid = (isset($_GET['cid'])) ? $_GET['cid'] : "";
		$string = "";
		$result = "";
		$pm = FALSE;
		$query = $this->EE->db->query("SELECT exp_members.group_id FROM exp_members, exp_sessions WHERE exp_sessions.session_id='$session' AND exp_sessions.member_id = exp_members.member_id");
		$group_id = $query->row('group_id');
		if ($group_id == 1) {
			$query = $this->EE->db->query("SELECT channel_id FROM exp_channels ORDER BY channel_title asc");
			foreach ($query->result_array() as $row) {
				if ($cid == $row['channel_id']) {
					$pm = TRUE;
				}
			}
		}
		else {
			$query = $this->EE->db->query("SELECT channel_id FROM exp_channel_member_groups WHERE group_id = '7'");
			foreach ($query->result_array() as $row) {
				$channel_id .= "channel_id = '" . $row['channel_id'] . "' OR ";
			}
			$channel_id = preg_replace("/OR $/", "", $channel_id);
			$query = $this->EE->db->query("SELECT channel_id, channel_title FROM exp_channels WHERE $channel_id ORDER BY channel_title asc");
			foreach ($query->result_array() as $row) {
				if ($cid == $row['channel_id']) {
					$pm = TRUE;
				}
			}
		}
		if (isset($_GET['eid'])) {
			$string = '<script type="text/javascript">$(document).ready(function() {$("#entry_id_search_acc").show();});</script>';
			$eid = $_GET['eid'];
			$query = $this->EE->db->query("SELECT entry_id, title, year, month, day FROM exp_channel_titles WHERE entry_id='$eid' AND channel_id='$cid'");
			$entry_id = $query->row('entry_id');
			$title = $query->row('title');
			$edit_date = $query->row('day') . '/' . $query->row('month') . '/' . $query->row('year');
			$result = "<div><table><tr><td style='padding: 0 10px;'>ID</td><td style='padding: 0 10px;'>Title</td><td style='padding: 0 10px;'>Edit Date</td></tr><tr><td style='padding: 0 10px;'>$entry_id</td><td style='padding: 0 10px;'><a href='/control-panel/index.php?S=$session&D=cp&C=content_publish&M=entry_form&channel_id=$cid&entry_id=$entry_id'>$title</a></td><td style='padding: 0 10px;'>$edit_date</td></tr></table></div>";
			if ($query->num_rows() == 0 || $pm === FALSE) {
				$result = "<div>No entry found with that ID.</div>";
			}
		}
		return "$string $result";
	}
}

/* End of file acc.entry_id_search.php */
/* Location: ./system/expressionengine/third_party/entry_id_search/acc.entry_id_search.php */  