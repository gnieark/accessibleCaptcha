<?php
/*
    Copyright 2009 Julien Wajsberg <felash@gmail.com>
    
    This file is part of dotclear-accessible-captcha.

    dotclear-accessible-captcha is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, version 3 of the License.

    Foobar is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Foobar.  If not, see <http://www.gnu.org/licenses/>.
*/

class dcFilterAccessibleCaptcha extends dcSpamFilter
{
	public $name = 'Accessible Captcha';
	public $has_gui = true;
	private $style_p = 'margin: .2em 0; padding: 0 0.5em; ';
	private $style_answer = 'margin: 0 0 0 .5em; ';
	
	protected function setInfo()
	{
		$this->description = __('This is an accessible captcha.');
	}
	
	public function isSpam($type,$author,$email,$site,$ip,$content,$post_id,&$status) {
    $accessibleCaptcha = new AccessibleCaptcha();
    	  
	  $question_hash = $_POST['c_question_hash'];
	  $answer = $_POST['c_answer'];
	  if (! $answer) {
  	  $status = 'Filtered';
	    return true;
	  }
	  
	  if (! $accessibleCaptcha->isAnswerCorrectForHash($question_hash, $answer)) {
  	  $status = 'Filtered';
	    return true;
	  }
  }
  
  public function getStatusMessage($status, $comment_id) {
	  return sprintf(__('Filtered by %s.'),$this->guiLink());
  }
  
  public static function publicCommentFormAfterContent($core, $_ctx) {
    $accessibleCaptcha = new AccessibleCaptcha();

    if (isset($_POST['c_question_hash'])){
      $question = $accessibleCaptcha->getQuestionForHash($_POST['c_question_hash']);
    } else {
      $question = $accessibleCaptcha->getRandomQuestionAndHash($core->blog->id);
    }
    
    $escaped_value = isset($_POST['c_answer']) ? htmlspecialchars($_POST['c_answer'], ENT_QUOTES) : '';
    $escaped_question = htmlspecialchars($question['question'], ENT_QUOTES);
    $escaped_hash = htmlspecialchars($question['hash'], ENT_QUOTES);
        
    echo "<p class='field'><label for='c_answer'>{$escaped_question}</label>
        <input name='c_answer' id='c_answer' type='text' size='30' maxlength='255' value='{$escaped_value}' />
        <input name='c_question_hash' id='c_question_hash' type='hidden' value='{$escaped_hash}' />
        </p>";
  }
  
  // plugin d'import export
  public static function exportFull($core, $exp) {
  	$exp->exportTable(AccessibleCaptcha::$table);
  }

  public static function exportSingle($core, $exp, $blog_id) {
	  $exp->export(AccessibleCaptcha::$table,
		  'SELECT * '.
		  'FROM '. $core->prefix . AccessibleCaptcha::$table . ' '.
      "WHERE blog_id = '{$blog_id}'"
	  );
  }
  // fin des méthodes pour le plugin d'import export
  
  // pour la gui
  public function gui($url) {
    global $core;
    
    $accessibleCaptcha = new AccessibleCaptcha();

    // ajout de questions
    if (! (empty($_POST['c_question']) || empty($_POST['c_answer']))) {
      $accessibleCaptcha->addQuestion(
        $core->blog->id,
        $_POST['c_question'],
        $_POST['c_answer']
      );
      
      // redirection pour que l'user puisse faire "reload"      
			http::redirect($url.'&added=1');
    }
    
    // suppression de questions
    if (! empty($_POST['c_d_questions']) && is_array($_POST['c_d_questions'])) {
      $accessibleCaptcha->removeQuestions($core->blog->id, $_POST['c_d_questions']);
      http::redirect($url.'&deleted=1');
    }
    
    // réinit
    if (! empty($_POST['c_createlist'])) {
      $accessibleCaptcha->initQuestions($core->blog->id);
      http::redirect($url.'&reset=1');
    }

    // assez joué, maintenant on affiche
    $res = '';
        
    if (!empty($_GET['added'])) {
      $res .= '<p class="message">'.__('Question has been successfully added.').'</p>';
    }
    if (!empty($_GET['deleted'])) {
      $res .= '<p class="message">'.__('Questions have been successfully removed.').'</p>';
    }
    if (!empty($_GET['reset'])) {
      $res .= '<p class="message">'.__('Questions list has been successfully reinitialized.').'</p>';
    }

    $res .=
		  '<form action="'.html::escapeURL($url).'" method="post">'.
		  '<fieldset><legend>'.__('Add a question').'</legend>'.
		  '<p><label>' . __('Question to add:') . ' '.
		  form::field('c_question', 40, 255) .
		  '</label></p>'.
		  '<p><label>' . __('Answer:') . ' '.
		  form::field('c_answer', 40, 255) .
		  '</label></p>';
		$res .=
		  $core->formNonce().
		  '<input type="submit" value="'.__('Add').'"/></p>'.
		  '</fieldset>'.
		  '</form>';

    $allquestions = $accessibleCaptcha->getAllQuestions($core->blog->id);
    
    			
    $res .= '<form action="'.html::escapeURL($url).'" method="post">'.
      '<fieldset><legend>' . __('Question list') . '</legend>';
      
    foreach($allquestions as $question) {
        $res .= '<p style="' . $this->style_p . '"><label class="classic">'.
  				form::checkbox('c_d_questions[]', $question['id']).
  				' <strong>'. __('Question:') . '</strong> '.
  				html::escapeHTML($question["question"]).
  				' <strong style="' . $this->style_answer . '">'. __('Answer:') . '</strong> '.
  				html::escapeHTML($question["answer"]).
				'</label></p>';
    }
    
    $res .= '<p>'. $core->formNonce() .
			'<input class="submit" type="submit" value="' . __('Delete selected questions') . '"/></p>';

    $res .= "</fieldset></form>";

		$res .=
			'<form action="'.html::escapeURL($url).'" method="post">' .
			'<p><input type="submit" value="' . __('Reset the list') . '" />' .
			form::hidden(array('c_createlist'), 1) .
			$core->formNonce().'</p>' .
      '</form>';

    $disableText = __('To disable this plugin, you need to disable it %sfrom the plugins page%s.');
    $disableText = sprintf($disableText, '<a href="plugins.php">', '</a>');

    $res .=
      '<p>'.$disableText.'</p>';

  	return $res;
  	
  }

}

