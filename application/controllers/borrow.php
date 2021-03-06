<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class borrow extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		//Load the models (which handle database queries)
		$this->load->model('gear_model');
		$this->load->model('people_model');
		$this->load->model('borrow_model');
		$this->load->model('u_model');

		//Check that the user has the correct rights to access the page, otherwise redirect to login page
		if ($_SESSION['admin']!=1){
			redirect('login/login');
		}
	}

	public function borrow(){
		// Borrow function:
		// get's the data needed to show the user the options for borrowing
		// it gets a list of gear and people
		$output['data']['gear']= $this->gear_model->get_avaliable();
		$output['data']['gear_fields']=$this->gear_model->get_fields();
		$output['data']['person_fields']=$this->people_model->get_fields();
		$output['data']['people']= $this->u_model->get_table('people');
		render('borrow/borrow',$output); //Send the data to the webpage
	}

	public function save($id=null){
		//Save function:
		// Records what gear was selected and what person is borrowing and writes that information to the database.

		// Collect the data the website sent
		$postData['gear']=json_decode($_POST['gear_selected'],TRUE);
		$postData['person']=json_decode($_POST['person_borrowing'],TRUE);
		$borrow_insert_data=array();

		//Go through the data that was sent and change it into the correct format for inserting into the database.
		foreach($postData['gear'] as $val){
			$temp_row=array( // Create a row to add to the array
				'gear_id'		=>$val['id'],
				'person_id'		=>$postData['person']['id'],
				'deposit'		=>$_POST['deposit'],
				'comment'		=>$_POST['comments'],
				'returned'		=>0);
			array_push($borrow_insert_data,$temp_row); // Store the row in the array
		}

		$this->borrow_model->insert($borrow_insert_data); // Send the array with all the data to the model to be added to the database

		redirect('borrow/view'); // Send the user to look at borrow tables once finished.
	}

	public function view()
	{
		// This function collects all the data from the model to display a few tables to the user.

		$borrow_fields=$this->borrow_model->get_fields(TRUE,FALSE,FALSE); //Get the fields which we normally display from a function
		
		$all_borrows=$this->borrow_model->get_stuff();

		// Fill in whether gear is overdue or not
		$overdue_borrows=$this->borrow_model->get_overdue(14);
		$overdue_list=array();
		foreach($overdue_borrows as $overdue_item){
			$overdue_list[$overdue_item['id']]=TRUE;
		}
		foreach($all_borrows as $index => $borrow_item){
			if (array_key_exists($borrow_item['id'], $overdue_list)){
				$all_borrows[$index]['overdue']="Yes";
			} else {
				$all_borrows[$index]['overdue']="No";
			}
		}

		// Enable or disable the next line to group by borrow_group_id
		$all_borrows=$this->by_borrow_group($all_borrows);

		$output['data']['row_data']= $all_borrows;
		$output['data']['title']='Borrowed Gear Information';
		$output['data']['subtitle']='All borrow events recorded in this system are included in this table';
		$output['data']['fields']=$borrow_fields;
		$output['data']['url']='';
		$output['data']['url_id']='';

		// dbg($output);
		render('gear/view',$output); //Send all the data to the view to be made into a webpage
	}

	public function view_return()
	{
		// This function shows a table which lets users pick out gear to return.
		$output['data']['row_data']= $this->borrow_model->get_stuff(array('returned'=>'0'));
		$output['data']['title']='Search for the gear you want to return';
		$output['data']['subtitle']='Click on gear to return it';
		$output['data']['fields']=$this->borrow_model->get_fields(FALSE,TRUE,TRUE,FALSE);
		$output['data']['url']='gear_return/';
		$output['data']['url_id']='borrow_group_id';
		// dbg($output);
		render('gear/view',$output);
	}

	public function gear_return($id){
		// This function collects the information about a group of gear which was all borrowed at once.
		// This function sends that information to the return page so that the user can choose which items to return.
		if ($id){
			$output['data']['gear']=$this->borrow_model->borrow_group_gear($id);
			$output['data']['fields']=$this->gear_model->get_fields();
			// dbg($output);
			render('borrow/return',$output);
		} else {
			redirect('borrow/view');
		}
	}

	public function save_return(){
		// This function collects the data about what gear the user has returned.
		// It then returns these items one by one.
		// Read the data which the webpage sent.
		$postData['gear']=json_decode($_POST['selectedGear'],TRUE);

		// Find out the borrow group this gear belongs to.
		$borrow_group=$postData['gear'][0]['borrow_group_id'];

		// Use a for loop to return each gear item seperately.
		foreach ($postData['gear'] as $temp_row){
			$this->borrow_model->process_return($borrow_group,$temp_row['gear_id']);
		}

		//Check if borrow group is completely returned.
		$remaining_gear=$this->u_model->get_table('borrow',array(
			'returned'=>0,
			'borrow_group_id'=>$borrow_group
			));
		
		// dbg(sizeof($remaining_gear));

		if (sizeof($remaining_gear)==0){
			// dbg(sizeof($remaining_gear));
			render('borrow/return_finished',$postData['gear'][0]);
		} else {
			redirect('borrow/view');
		}
	}
	public function email_list(){
		// Print out the email addresses of people with overdue gear.

		$overdue['email_list']=$this->borrow_model->get_overdue(14,TRUE); //Get a list of the overdue items
		// dbg($overdue);
		render('borrow/email_list',$overdue);
	}

	public function by_borrow_group($borrow_info){
		$grouped_info=array();
		foreach ($borrow_info as $item){
			if (!array_key_exists($item['borrow_group_id'], $grouped_info)){
				$grouped_info[$item['borrow_group_id']]=array();
			}
			array_push($grouped_info[$item['borrow_group_id']],$item);
		}

		$compress_grouped_data=array();
		foreach ($grouped_info as $item){
			array_push($compress_grouped_data,$this->compress_grouped($item));
		}

		// dbg($compress_grouped_data);
		return $compress_grouped_data;
	}

	public function compress_grouped($group_info){
		//overdue
		//gear names
		//date return
		//
		$grouped_info=array(
			'gear_name'=>'',
			'date_return'=>'',
			'returned'=>'',
			'overdue'=>'',
			'cat'=>'',
			);
		foreach ($group_info as $item){

			foreach ($item as $field => $value){
				if ($field == 'gear_name' ||
					$field == 'date_return' ||
					$field == 'returned' ||
					$field == 'cat' ||
					$field == 'overdue'){
					$grouped_info[$field]=$grouped_info[$field].'<div>'.$value.'</div>';
				} else {
					$grouped_info[$field]=$value;

				}
			}
		}
		// dbg($grouped_info);
		return $grouped_info;
	}
}