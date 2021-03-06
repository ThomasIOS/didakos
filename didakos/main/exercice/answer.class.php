<?php
/*
    DOKEOS - elearning and course management software

    For a full list of contributors, see documentation/credits.html

    This program is free software; you can redistribute it and/or
    modify it under the terms of the GNU General Public License
    as published by the Free Software Foundation; either version 2
    of the License, or (at your option) any later version.
    See "documentation/licence.html" more details.

    Contact:
		Dokeos
		Rue des Palais 44 Paleizenstraat
		B-1030 Brussels - Belgium
		Tel. +32 (2) 211 34 56
*/


/**
*	This class allows to instantiate an object of type Answer
*	5 arrays are created to receive the attributes of each answer belonging to a specified question
* 	@package dokeos.exercise
* 	@author Olivier Brouckaert
* 	@version $Id: answer.class.php 13732 2007-11-21 15:19:33Z yannoo $
*/


if(!class_exists('Answer')):


class Answer
{
	var $questionId;

	// these are arrays
	var $answer;
	var $correct;
	var $comment;
	var $weighting;
	var $position;
	var $hotspot_coordinates;
	var $hotspot_type;

	// these arrays are used to save temporarily new answers
	// then they are moved into the arrays above or deleted in the event of cancellation
	var $new_answer;
	var $new_correct;
	var $new_comment;
	var $new_weighting;
	var $new_position;
	var $new_hotspot_coordinates;
	var $new_hotspot_type;

	var $nbrAnswers;
	var $new_nbrAnswers;

/**
	 * constructor of the class
	 *
	 * @author 	Olivier Brouckaert
	 * @param 	integer	Question ID that answers belong to
	 */
	function Answer($questionId)
	{
		$this->questionType=$questionType;
		$this->questionId=$questionId;
		$this->answer=array();
		$this->correct=array();
		$this->comment=array();
		$this->weighting=array();
		$this->position=array();
		$this->hotspot_coordinates=array();
		$this->hotspot_type=array();

		// clears $new_* arrays
		$this->cancel();

		// fills arrays
		$this->read();
	}

	/**
	 * clears $new_* arrays
	 *
	 * @author - Olivier Brouckaert
	 */
	function cancel()
	{
		$this->new_answer=array();
		$this->new_correct=array();
		$this->new_comment=array();
		$this->new_weighting=array();
		$this->new_position=array();
		$this->new_hotspot_coordinates=array();
		$this->new_hotspot_type=array();

		$this->new_nbrAnswers=0;
	}

	/**
	 * reads answer informations from the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function read()
	{
		global $_course;
		$TBL_ANSWER = Database::get_course_table(TABLE_QUIZ_ANSWER);

		$questionId=$this->questionId;
		//$answerType=$this->selectType();

		$sql="SELECT answer,correct,comment,ponderation,position, hotspot_coordinates, hotspot_type FROM $TBL_ANSWER WHERE question_id='$questionId' ORDER BY position";

		$result=api_sql_query($sql,__FILE__,__LINE__);

		$i=1;

		// while a record is found
		while($object=mysql_fetch_object($result))
		{
			$this->answer[$i]=$object->answer;
			$this->correct[$i]=$object->correct;
			$this->comment[$i]=$object->comment;
			$this->weighting[$i]=$object->ponderation;
			$this->position[$i]=$object->position;
$this->hotspot_coordinates[$i]=$object->hotspot_coordinates;
			$this->hotspot_type[$i]=$object->hotspot_type;

			$i++;
		}

		$this->nbrAnswers=$i-1;
	}
	/**
	 * reads answer informations from the data base ordered by parameter
	 * @param	string	Field we want to order by
	 * @param	string	DESC or ASC
	 * @author 	Frederic Vauthier
	 */
	function readOrderedBy($field,$order=ASC)
	{
		global $_course;
		$field = mysql_real_escape_string($field);
		if(empty($field)){
			$field = 'position';
		}
		if($order != 'ASC' and $order!='DESC'){
			$order = 'ASC';
		}
		$TBL_ANSWER = Database::get_course_table('quiz_answer');

		$questionId=$this->questionId;
		//$answerType=$this->selectType();

		$sql="SELECT answer,correct,comment,ponderation,position, hotspot_coordinates, hotspot_type " .
				"FROM $TBL_ANSWER " .
				"WHERE question_id='$questionId' " .
				"ORDER BY $field $order";

		$result=api_sql_query($sql,__FILE__,__LINE__);

		$i=1;

		// while a record is found
		while($object=mysql_fetch_object($result))
		{
			$this->answer[$i]=$object->answer;
			$this->correct[$i]=$object->correct;
			$this->comment[$i]=$object->comment;
			$this->weighting[$i]=$object->ponderation;
			$this->position[$i]=$object->position;

			$i++;
		}

		$this->nbrAnswers=$i-1;
	}

	/**
	 * returns the number of answers in this question
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - number of answers
	 */
	function selectNbrAnswers()
	{
		return $this->nbrAnswers;
	}

	/**
	 * returns the question ID which the answers belong to
	 *
	 * @author - Olivier Brouckaert
	 * @return - integer - the question ID
	 */
	function selectQuestionId()
	{
		return $this->questionId;
	}

	/**
	 * returns the answer title
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer title
	 */
	function selectAnswer($id)
	{
		return $this->answer[$id];
	}

	/**
	 * Returns a list of answers
	 * @author Yannick Warnier <ywarnier@beeznest.org>
	 * @return array	List of answers where each answer is an array of (id, answer, comment, grade) and grade=weighting
	 */
	 function getAnswersList()
	 {
	 	$list = array();
	 	for($i = 1; $i<=$this->nbrAnswers;$i++){
	 		if(!empty($this->answer[$i])){
	 			$list[] = array(
						'id'=>$i,
						'answer'=>addslashes($this->answer[$i]),
						'comment'=>$this->comment[$i],
						'grade' => $this->weighting[$i],
						'hotspot_coord' => $this->hotspot_coordinates[$i],
						'hotspot_type'	=> $this->hotspot_type[$i],
						'correct'		=> $this->correct[$i]
				);
	 		}
	 	}
	 	return $list;
	 }
	/**
	 * Returns a list of grades
	 * @author Yannick Warnier <ywarnier@beeznest.org>
	 * @return array	List of grades where grade=weighting (?)
	 */
	 function getGradesList()
	 {
	 	$list = array();
	 	for($i = 0; $i<$this->nbrAnswers;$i++){
	 		if(!empty($this->answer[$i])){
	 			$list[$i] = $this->weighting[$i];
	 		}
	 	}
	 	return $list;
	 }

	 /**
	  * Returns the question type
	  * @author	Yannick Warnier <ywarnier@beeznest.org>
	  * @return	integer	The type of the question this answer is bound to
	  */
	 function getQuestionType()
	 {
	 	$TBL_QUESTIONS = Database::get_course_table(TABLE_QUIZ_QUESTION);
	 	$sql = "SELECT * FROM $TBL_QUESTIONS WHERE id = '".$this->questionId."'";
	 	$res = api_sql_query($sql,__FILE__,__LINE__);
	 	if(Database::num_rows($res)<=0){
	 		return null;
	 	}
	 	$row = Database::fetch_array($res);
	 	return $row['type'];
	 }


	/**
	 * tells if answer is correct or not
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - 0 if bad answer, not 0 if good answer
	 */
	function isCorrect($id)
	{
		return $this->correct[$id];
	}

	/**
	 * returns answer comment
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - string - answer comment
	 */
	function selectComment($id)
	{
		return $this->comment[$id];
	}

	/**
	 * returns answer weighting
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer weighting
	 */
	function selectWeighting($id)
	{
		return $this->weighting[$id];
	}

	/**
	 * returns answer position
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $id - answer ID
	 * @return - integer - answer position
	 */
	function selectPosition($id)
	{
		return $this->position[$id];
	}

	/**
	 * returns answer hotspot coordinates
	 *
	 * @author	Olivier Brouckaert
	 * @param	integer	Answer ID
	 * @return	integer	Answer position
	 */
	function selectHotspotCoordinates($id)
	{
		return $this->hotspot_coordinates[$id];
	}

	/**
	 * returns answer hotspot type
	 *
	 * @author	Toon Keppens
	 * @param	integer		Answer ID
	 * @return	integer		Answer position
	 */
	function selectHotspotType($id)
	{
		return $this->hotspot_type[$id];
	}

	/**
	 * creates a new answer
	 *
	 * @author Olivier Brouckaert
	 * @param string 	answer title
	 * @param integer 	0 if bad answer, not 0 if good answer
	 * @param string 	answer comment
	 * @param integer 	answer weighting
	 * @param integer 	answer position
	 * @param coordinates 	Coordinates for hotspot exercises (optional)
	 * @param integer		Type for hotspot exercises (optional)
	 */
	function createAnswer($answer,$correct,$comment,$weighting,$position,$new_hotspot_coordinates = NULL, $new_hotspot_type = NULL)
	{
		$this->new_nbrAnswers++;

		$id=$this->new_nbrAnswers;

		$this->new_answer[$id]=$answer;
		$this->new_correct[$id]=$correct;
		$this->new_comment[$id]=$comment;
		$this->new_weighting[$id]=$weighting;
		$this->new_position[$id]=$position;
		$this->new_hotspot_coordinates[$id]=$new_hotspot_coordinates;
		$this->new_hotspot_type[$id]=$new_hotspot_type;
	}

	/**
	 * updates an answer
	 *
	 * @author Toon Keppens
	 * @param	string	Answer title
	 * @param	string	Answer comment
	 * @param	integer	Answer weighting
	 * @param	integer	Answer position
	 */
	function updateAnswers($answer,$comment,$weighting,$position)
	{
		global $TBL_REPONSES;

		$questionId=$this->questionId;

		$sql = "UPDATE $TBL_REPONSES SET " .
				"`answer` = '$answer', " .
				"`comment` = '$comment', " .
				"`ponderation` = '$weighting', " .
				"`position` = '$position' " .
				"WHERE `id` =$position " .
				"AND `question_id` =$questionId";

		api_sql_query($sql,__FILE__,__LINE__);
	}

	/**
	 * records answers into the data base
	 *
	 * @author - Olivier Brouckaert
	 */
	function save()
	{
		global $TBL_REPONSES;

		$questionId=$this->questionId;

		// removes old answers before inserting of new ones
		$sql="DELETE FROM $TBL_REPONSES WHERE question_id='$questionId'";
		api_sql_query($sql,__FILE__,__LINE__);

		// inserts new answers into data base
		$sql="INSERT INTO $TBL_REPONSES" .
				"(id,question_id,answer,correct,comment," .
				"ponderation,position,hotspot_coordinates,hotspot_type) VALUES";

		for($i=1;$i <= $this->new_nbrAnswers;$i++)
		{
			$answer=addslashes($this->new_answer[$i]);
			$correct=$this->new_correct[$i];
			$comment=addslashes($this->new_comment[$i]);
			$weighting=$this->new_weighting[$i];
			$position=$this->new_position[$i];
			$hotspot_coordinates=$this->new_hotspot_coordinates[$i];
			$hotspot_type=$this->new_hotspot_type[$i];

			$sql.="('$i','$questionId','$answer','$correct','$comment',
					'$weighting','$position','$hotspot_coordinates','$hotspot_type'),";
		}

		$sql = substr($sql,0,-1);

		api_sql_query($sql,__FILE__,__LINE__);

		// moves $new_* arrays
		$this->answer=$this->new_answer;
		$this->correct=$this->new_correct;
		$this->comment=$this->new_comment;
		$this->weighting=$this->new_weighting;
		$this->position=$this->new_position;
		$this->hotspot_coordinates=$this->new_hotspot_coordinates;
		$this->hotspot_type=$this->new_hotspot_type;

		$this->nbrAnswers=$this->new_nbrAnswers;

		// clears $new_* arrays
		$this->cancel();
	}

	/**
	 * duplicates answers by copying them into another question
	 *
	 * @author - Olivier Brouckaert
	 * @param - integer $newQuestionId - ID of the new question
	 */
	function duplicate($newQuestionId)
	{
		global $TBL_REPONSES;

		// if at least one answer
		if($this->nbrAnswers)
		{
			// inserts new answers into data base
			$sql="INSERT INTO $TBL_REPONSES" .
					"(id,question_id,answer,correct,comment," .
					"ponderation,position,hotspot_coordinates,hotspot_type) VALUES";

			for($i=1;$i <= $this->nbrAnswers;$i++)
			{
				$answer=addslashes($this->answer[$i]);
				$correct=$this->correct[$i];
				$comment=addslashes($this->comment[$i]);
				$weighting=$this->weighting[$i];
				$position=$this->position[$i];
				$hotspot_coordinates=$this->hotspot_coordinates[$i];
				$hotspot_type=$this->hotspot_type[$i];

				$sql.="('$i','$newQuestionId','$answer','$correct','$comment'," .
						"'$weighting','$position','$hotspot_coordinates','$hotspot_type'),";
			}

			$sql=substr($sql,0,-1);
			api_sql_query($sql,__FILE__,__LINE__);
		}
	}
}

endif;
?>
