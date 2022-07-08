<?php

/**
* Модель для роботи з мультимовністю
*/
class wl_language_model
{
	
	private $words = array();
	private $words_aliases = array(0);

	public function get($word, $alias = -1)
	{
		if(empty($this->words))
			$this->getWords($alias);
		if($alias > 0 && !in_array($alias, $this->words_aliases))
			$this->getWords($alias);
		$word = trim($word);
		if(array_key_exists($word, $this->words))
		{
			if($this->words[$word] != '')
				return $this->words[$word];
		}
		else
			$this->add($word, $alias);
		return $word;
	}

	public function add($word, $alias = -1)
	{
		if(empty(trim($word)))
			return true;
		$this->words[$word] = $word;
		$data['word'] = $word;
		$data['alias'] = $_SESSION['alias']->id;
		$data['type'] = 1;
		if($alias >= 0)
			$data['alias'] = $alias;
		$data['position'] = $this->db->getCount('wl_language_words', $data['alias'], 'alias') + 1;
		if($id = $this->db->insertRow('wl_language_words', $data))
		{
			if($_SESSION['language'])
				foreach ($_SESSION['all_languages'] as $language) {
					$this->db->insertRow('wl_language_values', array('word' => $id, 'language' => $language));
				}
			else
				$this->db->insertRow('wl_language_values', array('word' => $id));
			$this->clearSessionCacheForAlias($alias);
		}
		return true;
	}

	public function getAllWords()
	{
		$this->db->executeQuery('SET SQL_BIG_SELECTS=1');
		$this->db->select('wl_language_words as w');
		if($_SESSION['language'])
			foreach ($_SESSION['all_languages'] as $language) {
				$this->db->join("wl_language_values as language_{$language}", "value as {$language}", array('language' => $language, 'word' => '#w.id'));
			}
		else
			$this->db->join("wl_language_values", "value", array('word' => '#w.id'));
		$this->db->order('position');
		return $this->db->get('array');
	}

	private function getWords($alias = -1)
	{
		$cache_alias = false;
		if(empty($_SESSION['alias']->alias))
			$cache_alias = 'wl_aliases';
		$this->words = array();
		if($alias == -1 || empty($this->words))
	        if($cache = $this->db->cache_get('textWords', $cache_alias))
			{
				$this->words = $cache['words'];
				$this->words_aliases = $cache['words_aliases'];
				if($alias == -1 || in_array($alias, $this->words_aliases))
					return true;
			}
		if(!in_array($_SESSION['alias']->id, $this->words_aliases))
			$this->words_aliases[] = $_SESSION['alias']->id;
		if($alias > 0 && !in_array($alias, $this->words_aliases))
			$this->words_aliases[] = $alias;
		if($alias == -1 || empty($this->words))
			$where['alias'] = $this->words_aliases;
		else
			$where['alias'] = $alias;
		$this->db->select('wl_language_words as w', 'id, alias, word', $where);
		if($_SESSION['language'])
			$this->db->join('wl_language_values', 'value', array('language' => $_SESSION['language'], 'word' => '#w.id'));
		else
			$this->db->join('wl_language_values', 'value', array('word' => '#w.id'));
		$this->db->order('alias');
		if($words = $this->db->get('array'))
			foreach ($words as $word) {
				$word->word = trim($word->word);
				if(array_key_exists($word->word, $this->words))
				{
					$this->db->deleteRow('wl_language_words', $word->id);
					$this->db->deleteRow('wl_language_values', $word->id, 'word');
				}
				else
					$this->words[$word->word] = $word->value;
			}
		$this->db->cache_add('textWords', ['words_aliases' => $this->words_aliases, 'words' => $this->words], $cache_alias);
		return true;
	}

	public function save($word, $language = false, $value = '', $rewrite = true)
	{
		$where['word'] = $word;
		if($language)
			$where['language'] = $language;
		$translate = $this->db->getAllDataById('wl_language_values', $where);
		if($translate)
		{
			if($rewrite || $translate->value == '')
				$this->db->updateRow('wl_language_values', array('value' => $value), $translate->id);
		}
		else
		{
			$where['value'] = $value;
			$this->db->insertRow('wl_language_values', $where);
		}
		if($rewrite)
			if($word = $this->db->getAllDataById('wl_language_words', $word))
				$this->clearSessionCacheForAlias($word->alias);
		return true;
	}

	public function copy($alias, $language = false)
	{
		if($words = $this->db->getAllDataByFieldInArray('wl_language_words', $alias, 'alias'))
			foreach ($words as $word) {
				$this->save($word->id, $language, $word->word, false);
			}
		$this->clearSessionCacheForAlias($alias);
		return true;
	}

	private function clearSessionCacheForAlias($alias = 0)
	{
		if($alias > 0 && $alias == $_SESSION['alias']->id)
			$this->db->cache_delete('textWords');
		else
		{
			$aliases = $this->db->select('wl_aliases', 'alias as uri')->get('array');
			foreach ($aliases as $alias) {
				$this->db->cache_delete('textWords', $alias->uri);
			}
		}
		return true;
	}

}

?>