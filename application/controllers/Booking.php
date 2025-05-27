<?php  

	class Booking extends CI_Controller
	{
		public function index()
		{			
			$data['bookinglist'] = $this->Booking_mdl->tourguidebookinglist();
			$data['innerdata'] = 'tourguide_bookinglist';
			$data['todayrequestlist'] = $this->Request_mdl->todayrequestlist();
			$data['todaybookinglist'] = $this->Booking_mdl->todaybookinglist();
			$data['todayratelist'] = $this->Review_mdl->todayratelist();
			$this->load->view('tourguidetemplate', $data);
		}

		public function add()
		{
			$this->load->helper(array('form', 'url'));
			$this->load->library('form_validation');

			// select 
			$id = $this->uri->segment(3);
			$data['showtour'] = $this->Tour_mdl->show($id);

			// checkdate
			$duration = $data['showtour'][0]->duration;

			$bookdate = $this->uri->segment(4).'-'.$this->uri->segment(5).'-'.$this->uri->segment(6);
			$todate = date('Y-m-d', strtotime($bookdate))." + ".($duration-1)." day";
			$todate = date('Y-m-d', strtotime($todate));
			$datearray = $this->Tour_mdl->getDatesFromRange($bookdate, $todate); 

			$checkbookingdate = $this->Tour_mdl->checkbookingdate($id);

			if (array_intersect($datearray, $checkbookingdate)) {
				echo "<script>alert('Sorry, tourguide is not available in the following day. Please Choose the date again.');</script>";
				redirect(base_url().'index.php/Booking/calendar/'.$id.'/'.$this->uri->segment(4).'/'.$this->uri->segment(5),'refresh');
			} else {
				$data['innerdata'] = 'booking';
				$this->load->view('template', $data);
			}

			

		}


		// calendar  
		public function calendar() 
		{
			$id = $this->uri->segment(3);
			$urlyear = $this->uri->segment(4);
			$urlmonth = $this->uri->segment(5);

			$checkbookingdate = $this->Tour_mdl->checkbookingdate($id);
			$nowyear = date('Y');
			$nowmonth = date('m');
			$nowday = date('d');

			// Correct day calculation
			$endday = date('t', strtotime("$urlyear-$urlmonth-01")); // Gets last day of month
			
			$datedata = []; // Proper array initialization

			// Determine start day
			$startday = 1;
			if ($nowyear == $urlyear && $nowmonth == $urlmonth) {
				$startday = $nowday + 3; // Start 3 days after current day
			}

			// Generate available dates
			for ($i = $startday; $i <= $endday; $i++) {
				$day = str_pad($i, 2, '0', STR_PAD_LEFT);
				$date = "$urlyear-$urlmonth-$day";
				
				if (!in_array($date, $checkbookingdate)) {
					$datedata[$i] = base_url("index.php/Booking/add/$id/$urlyear/$urlmonth/$day");
				}
			}

			// Calendar setup
			$this->load->library('calendar');
			$this->calendar->day_type = 'short';
			$this->calendar->show_next_prev = true;
			$this->calendar->next_prev_url = site_url('Booking/calendar/'.$id);

			$data['calendar'] = $this->calendar->generate($urlyear, $urlmonth, $datedata);
			$data['innerdata'] = 'calendar';
			
			$this->load->view('template', $data);
		}

		// store 
		public function store()
		{
			$this->form_validation->set_rules('starttime', 'StartTime', 'required', array('required' => 'Please Choose Start Time!'));
			$this->form_validation->set_rules('nooftotalpeople', 'TotalPeople', 'required', array('required' => 'Please Choose Total People!'));
			$this->form_validation->set_rules('month', 'Month', 'required', array('required' => 'Please Choose Expiry Month!'));
			$this->form_validation->set_rules('year', 'Year', 'required', array('required' => 'Please Choose Expiry Year!'));

			$id = $this->input->post('tourid');

			if ($this->form_validation->run() == FALSE)
            {
				$data['showtour'] = $this->Tour_mdl->show($id);

				$data['innerdata'] = 'booking';
				$this->load->view('template', $data);
            }
            else
            {
                $bookingid = $this->Booking_mdl->store();
                echo "<script type='text/javascript'>alert('Booking Process is Successfully Completed!');</script>";
                redirect(base_url() . 'index.php/Booking/detail/'.$bookingid, 'refresh');
            }
		}

		public function detail()
		{
			$id = $this->uri->segment(3);
			$data['detailbooking'] = $this->Booking_mdl->detail($id);
			$data['innerdata'] = 'booking_detail';
			$this->load->view('template', $data);
		}

		public function show()
		{
			$id = $this->uri->segment(3);
			$data['detailbooking'] = $this->Booking_mdl->detail($id);
			$data['innerdata'] = 'booking_show';
				
			if ($this->session->userdata('role') == 'staff') {
				$data['todaytourlist'] = $this->Tour_mdl->todaytourlist();
     			$data['notidata'] = 'adminnoti_tour';
				$this->load->view('stafftemplate', $data);
			} else {
				$data['todayrequestlist'] = $this->Request_mdl->todayrequestlist();
				$data['todaybookinglist'] = $this->Booking_mdl->todaybookinglist();
				$data['todayratelist'] = $this->Review_mdl->todayratelist();
				$this->load->view('tourguidetemplate', $data);
			}
		}

		public function bookinglist()
		{
			$data['requestlist'] = $this->Request_mdl->requestlist($this->session->userdata('id'));
			$data['bookinglist'] = $this->Booking_mdl->bookinglist($this->session->userdata('id'));
			$data['innerdata'] = 'booking_list';
			$this->load->view('template', $data);
		}

		public function cancel()
		{
			$id = $this->uri->segment(3);
			$this->Booking_mdl->cancel($id);
			echo "<script type='text/javascript'>alert('Your Cancellation Request is Successfully Done.');</script>";
			redirect(base_url() . 'index.php/Booking/detail/'.$id, 'refresh');
		}

		public function showall()
		{
			$data['bookinglist'] = $this->Booking_mdl->showall();
			$data['innerdata'] = 'booking_showall';
			$data['tourtypelist'] = $this->Tourtype_mdl->tourtypelist();
			$data['regionlist'] = $this->Region_mdl->regionlist();
     		$data['todaytourlist'] = $this->Tour_mdl->todaytourlist();
			$data['notidata'] = 'adminnoti_tour';
			$this->load->view('stafftemplate', $data);
		}
	}
?>