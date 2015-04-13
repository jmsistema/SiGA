<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(APPPATH."/constants/EnrollmentConstants.php");

class Request_model extends CI_Model {

	public function saveNewRequest($student, $course, $semester){

		define("INCOMPLETE", "incomplete");

		$requestData = array(
			'id_student' => $student,
			'id_course' => $course,
			'id_semester' => $semester,
			'request_status' => INCOMPLETE
		);

		$registeredRequest = $this->getRequest($requestData);

		if($registeredRequest !== FALSE){
			$requestId = $registeredRequest['id_request'];
		}else{

			$this->db->insert('student_request', $requestData);

			$foundRequest = $this->getRequest($requestData);

			if($foundRequest !== FALSE){
				$requestId = $foundRequest['id_request'];
			}else{
				$requestId = FALSE;
			}
		}

		return $requestId;
	}

	public function saveDisciplineRequest($requestId, $idOfferDiscipline, $status){

		$requestDiscipline = array(
			'id_request' => $requestId,
			'discipline_class' => $idOfferDiscipline,
			'status' => $status
		);

		$this->db->insert('request_discipline', $requestDiscipline);

		$foundRequest = $this->getRequestDisciplines($requestDiscipline);

		if($foundRequest !== FALSE){
			$wasSaved = TRUE;
		}else{
			$wasSaved = FALSE;
		}

		return $wasSaved;
	}

	public function approveAllRequest($requestId){

		$wasApproved = $this->changeAllRequest($requestId, EnrollmentConstants::ENROLLED_STATUS);

		return $wasApproved;
	}
	
	public function mastermindApproveAllCurrentStudentRequest($studentId, $currentSemester, $requestId){
		
		$wasApproved = $this->changeStudentRequestsStatus($studentId, $currentSemester, EnrollmentConstants::ENROLLED_STATUS, $requestId);
		
		return $wasApproved;
	}

	public function refuseAllRequest($requestId){

		$wasRefused = $this->changeAllRequest($requestId, EnrollmentConstants::REFUSED_STATUS);

		return $wasRefused;
	}

	public function mastermindRefuseAllCurrentStudentRequest($studentId, $currentSemester, $requestId){
	
		$wasRefused = $this->changeStudentRequestsStatus($studentId, $currentSemester, EnrollmentConstants::REFUSED_STATUS, $requestId);
	
		return $wasRefused;
	}
	private function changeAllRequest($requestId, $newStatus){

		$requestDisciplines = $this->getRequestDisciplinesById($requestId);

		if($requestDisciplines !== FALSE){

			foreach($requestDisciplines as $requestedDiscipline){
				
				$this->changeRequestDisciplineStatus($requestId, $requestedDiscipline['discipline_class'], $newStatus);
			}

			$this->checkRequestGeneralStatus($requestId);
			$wasChanged = TRUE;

		}else{

			$wasChanged = FALSE;
		}

		return $wasChanged;
	}

	private function changeStudentRequestsStatus($studentId, $currentSemester, $newStatus, $requestId){
		$hasRequests = $this->checkStudentHasRequest($studentId);
		
		if ($hasRequests ==! FALSE){
			$requestDisciplines = $this->getRequestDisciplinesById($requestId);
			if ($requestDisciplines){
				foreach ($requestDisciplines as $requestedDisciplines){
					$this->changeRequestDisciplineStatus($requestId, $requestedDisciplines['discipline_class'], $newStatus);
				}
				$this->changeStudentStatusRequest($studentId, $currentSemester, $newStatus);
				$wasChanged = TRUE;
			}else{
				$wasChanged = FALSE;
			}
		}else{
			$wasChanged = FALSE;
		}
		
		return $wasChanged;
	}
	
	private function changeStudentStatusRequest($studentId, $currentSemester, $newStatus){
		$where = array('id_student'=>$studentId, 'id_semester'=> $currentSemester);
		$change = array('request_status'=>$newStatus);
		$this->db->where($where);
		$this->db->update('student_request', $change);
	}

	private function checkStudentHasRequest($studentId){
		$this->db->select('id_request');
		$this->db->where('id_student', $studentId);
		$this->db->from('student_request');
		$hasRequest = $this->db->get()->result_array();
		
		$hasRequest = checkArray($hasRequest);
		
		return $hasRequest;
	}
	public function approveRequestedDiscipline($requestId, $idOfferDiscipline){

		$wasApproved = $this->changeRequestDisciplineStatus($requestId, $idOfferDiscipline, EnrollmentConstants::ENROLLED_STATUS);

		$this->checkRequestGeneralStatus($requestId);

		return $wasApproved;
	}

	public function refuseRequestedDiscipline($requestId, $idOfferDiscipline){

		$wasRefused = $this->changeRequestDisciplineStatus($requestId, $idOfferDiscipline, EnrollmentConstants::REFUSED_STATUS);

		$this->checkRequestGeneralStatus($requestId);

		return $wasRefused;
	}

	private function checkRequestGeneralStatus($requestId){

		$wasAllApproved = $this->checkIfRequestWasAllApprovedOrRefused($requestId, EnrollmentConstants::ENROLLED_STATUS);
		$wasAllRefused = $this->checkIfRequestWasAllApprovedOrRefused($requestId, EnrollmentConstants::REFUSED_STATUS);
		$hasPreEnrolled = $this->checkIfRequestHasPreEnrolled($requestId);

		if($wasAllApproved){
			$status = EnrollmentConstants::REQUEST_ALL_APPROVED_STATUS;
		}else if($wasAllRefused){
			$status = EnrollmentConstants::REQUEST_ALL_REFUSED_STATUS;
		}else if($hasPreEnrolled){
			$status = EnrollmentConstants::REQUEST_INCOMPLETE_STATUS;
		}else{
			$status = EnrollmentConstants::REQUEST_PARTIALLY_APPROVED_STATUS;
		}
		
		$this->changeRequestGeneralStatus($requestId, $status);
	}

	private function changeRequestGeneralStatus($requestId, $newStatus){

		$this->db->where('id_request', $requestId);
		$this->db->update('student_request', array('request_status' => $newStatus));
	}

	private function checkIfRequestHasPreEnrolled($requestId){

		$requestDisciplines = $this->getRequestDisciplinesById($requestId);

		if($requestDisciplines !== FALSE){

			$hasPreEnrolled = FALSE;
			foreach($requestDisciplines as $requestedDiscipline){
				
				if($requestedDiscipline['status'] === EnrollmentConstants::PRE_ENROLLED_STATUS){
					$hasPreEnrolled = TRUE;
					break;
				}
			}
		}else{
			$hasPreEnrolled = FALSE;
		}

		return $hasPreEnrolled;
	}

	private function checkIfRequestWasAllApprovedOrRefused($requestId, $statusToCheck){

		$requestDisciplines = $this->getRequestDisciplinesById($requestId);

		if($requestDisciplines !== FALSE){

			$disciplinesWithStatus = 0;

			// Check if all disciplines has the given status
			foreach($requestDisciplines as $requestedDiscipline){
				
				if($requestedDiscipline['status'] === $statusToCheck){
					$disciplinesWithStatus++;
				}
			}

			$quantityOfDisciplines = sizeof($requestDisciplines);

			// In this case all disciplines as approved
			if($quantityOfDisciplines === $disciplinesWithStatus){
				$wasAll = TRUE;
			}else{
				$wasAll = FALSE;
			}

		}else{
			$wasAll = FALSE;
		}

		return $wasAll;
	}

	private function changeRequestDisciplineStatus($requestId, $idOfferDiscipline, $newStatus){

		$this->db->where("id_request", $requestId);
		$this->db->where("discipline_class", $idOfferDiscipline);
		$this->db->update('request_discipline', array('status' => $newStatus));

		$requestDisciplineData = array(
			'id_request' => $requestId,
			'discipline_class' => $idOfferDiscipline,
			'status' => $newStatus
		);

		$foundRequestDiscipline = $this->getRequestDisciplines($requestDisciplineData);

		$wasChanged = $foundRequestDiscipline !== FALSE;

		return $wasChanged;
	}

	public function getStudentRequests($courseId, $semesterId, $studentIds){

		$this->db->select("student_request.*");
		$this->db->distinct();
		$this->db->from("student_request");
		$this->db->join("request_discipline", "student_request.id_request = request_discipline.id_request");
		$this->db->where("student_request.id_course", $courseId);
		$this->db->where("student_request.id_semester", $semesterId);
		
		$ids = $studentIds;

		$whereClause = "(";
		foreach($studentIds as $key => $studentId){
			
			if(hasNext($ids)){
				unset($ids[$key]);
				$whereClause = $whereClause."student_request.id_student = {$studentId} OR ";
			}else{
				$whereClause = $whereClause." student_request.id_student = {$studentId}";
			}
		}
		$whereClause = $whereClause.")";

		$this->db->where($whereClause);
		$this->db->order_by("request_status", "asc");

		$foundRequest = $this->db->get()->result_array();

		$foundRequest = checkArray($foundRequest);

		return $foundRequest;
	}

	public function getRequestDisciplinesById($requestId){

		$disciplines = $this->getRequestDisciplines(array('id_request' => $requestId));

		return $disciplines;
	}

	private function getRequestDisciplines($requestDisciplineData){

		$foundRequestDiscipline = $this->db->get_where('request_discipline', $requestDisciplineData)->result_array();

		$foundRequestDiscipline = checkArray($foundRequestDiscipline);

		return $foundRequestDiscipline;
	}
	
	public function getRequestCourseId($requestId){
		
		$this->db->select('id_course');
		$this->db->from('student_request');
		$this->db->where('id_request', $requestId);
		$course_id = $this->db->get()->row_array();
		$course_id = checkArray($course_id);
		
		return $course_id;
		
	}

	public function getUserRequestDisciplines($userId, $courseId, $semesterId){

		$requestData = array(
			'id_student' => $userId,
			'id_course' => $courseId,
			'id_semester' => $semesterId
		);

		$request = $this->request_model->getRequest($requestData);

		if($request !== FALSE){

			$requestStatus = $request['request_status'];

			$classes = $this->getRequestDisciplinesClasses($request['id_request']);

			$requestDisciplinesClasses = array(
				'requestStatus' => $requestStatus,
				'requestDisciplinesClasses' => $classes
			);
			
		}else{
			$requestDisciplinesClasses = FALSE;
		}

		return $requestDisciplinesClasses;
	}
	
	public function getMastermindMessage($studentId, $courseId, $semesterId){
		$requestData = array(
				'id_student' => $studentId,
				'id_course' => $courseId,
				'id_semester' => $semesterId
		);
		
		$request = $this->request_model->getRequest($requestData);
		
		if($request !== FALSE){
			$message = $this->getMastermindMessageForStudent($studentId, $request['id_request']);
		}else{
			$message = 'Seu Orientador não deixou mensagem.';
		}
		
		return $message;
	}

	public function getRequestDisciplinesClasses($requestId){

		$this->db->select('offer_discipline.*, request_discipline.status');
		$this->db->from('request_discipline');
		$this->db->join('offer_discipline', "request_discipline.discipline_class = offer_discipline.id_offer_discipline");
		$this->db->where('request_discipline.id_request', $requestId);
		$foundClasses = $this->db->get()->result_array();

		$foundClasses = checkArray($foundClasses);

		return $foundClasses;
	}

	private function getMastermindMessageForStudent($studentId, $requestId){
		$where = array(
			'id_request' => $requestId,
			'id_student' => $studentId
		);
		$mastermindMessage = $this->db->get_where('mastermind_message', $where)->row_array();
		
		$mastermindMessage = checkArray($mastermindMessage);
		
		if ($mastermindMessage){
			$message = $mastermindMessage['message'];
		}else{
			$message = 'Seu Orientador não deixou mensagem.';
		}
		
		return $message;
	}
	
	public function getRequest($requestData){

		$foundRequest = $this->db->get_where('student_request', $requestData)->row_array();

		$foundRequest = checkArray($foundRequest);

		return $foundRequest;
	}

	public function getCourseRequests($courseId, $semesterId){

		$this->db->select("student_request.*");
		$this->db->distinct();
		$this->db->from("student_request");
		$this->db->join("request_discipline", "student_request.id_request = request_discipline.id_request");
		$this->db->where("student_request.id_course", $courseId);
		$this->db->where("student_request.id_semester", $semesterId);
		$this->db->order_by("request_status", "asc");
		
		$foundRequest = $this->db->get()->result_array();
		$foundRequest = checkArray($foundRequest);
		
		return $foundRequest;
	}
	
	public function getMastermindStudentRequest($studentId, $semesterId){
	
		$this->db->select("student_request.*, request_discipline.*");
		$this->db->from("student_request");
		$this->db->join("request_discipline", "student_request.id_request = request_discipline.id_request");
		$this->db->where("student_request.id_student", $studentId);
		$this->db->where("student_request.id_semester", $semesterId);
		$this->db->order_by("request_status", "asc");
	
		$foundRequest = $this->db->get()->result_array();
	
		$foundRequest = checkArray($foundRequest);
	
		return $foundRequest;
	}
	
	public function saveMasterindMessage($message, $requestId=NULL, $studentId, $mastermindId, $idOfferDiscipline=NULL){
		$messageData = array(
				'id_mastermind' => $mastermindId,
				'id_student' => $studentId,
				'id_request' => $requestId,
				'id_offer_discipline' => $idOfferDiscipline,
				'message' => $message
		);
			
		$messageExist = $this->checkExistingMessage($messageData);
		
		if ($messageExist){
			$savedMessage = $this->updateMessageInDb($messageData);
		}else{
			$savedMessage = $this->insertMessageInDb($messageData);
		}
		return $savedMessage;
	}
	
	private function checkExistingMessage($messageData){
		
		$existingMessage = $this->db->get_where('mastermind_message', $messageData);
		
		return $existingMessage;
	}
	
	private function updateMessageInDb($messageData){
		$where = array(
				'id_student'=>$messageData['id_student'],
				'id_mastermind'=>$messageData['id_mastermind'],
				'id_request'=>$messageData['id_request']
		);
		
		$this->db->where($where);
		$updatedMessageData = $this->db->update('mastermind_message', $messageData);
		return $updatedMessageData;
	}
	
	private function insertMessageInDb($messageData){
		$savedMessage = $this->db->insert('mastermind_message', $messageData);
		return $savedMessage;
	}
}
