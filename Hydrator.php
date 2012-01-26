<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Hydrator {

	function hydrate($objects, $with)
	{
		get_instance()->load->database();

		$sample = $objects[0];

		foreach ($with as $related)
		{
			$related = trim($related); // remove extra white space if present

			if (array_key_exists($related, $sample->belongs_to()))
			{
				$belongs_to = $sample->belongs_to(); 
				//$objects = $this->hydrate_belongs_to($belongs_to[$related], $objects);
				$objects = $this->hydrate_belongs_to($belongs_to[$related], $objects);
			}

			if (array_key_exists($related, $sample->has_many()))
			{
				$has_many = $sample->has_many();
				$objects = $this->hydrate_has_many($has_many[$related], $objects);
			}

			if (array_key_exists($related, $sample->has_and_belongs_to_many()))
			{
				$has_and_belongs_to_many = $sample->has_and_belongs_to_many();
				$objects = $this->hydrate_has_and_belongs_to_many($has_and_belongs_to_many[$related], $objects);
			}
		}

		return $objects;
	}

	function hydrate_belongs_to($relation, &$objects) 
	{
		require_once APPPATH."models/{$relation['model']}.php";
		$ci = get_instance();
		$related_ids = array();
		foreach ($objects as $object)
		{
			$related_ids[] = $object->{$relation['with_key']};
		}
		$related_key = isset($relation['related_key']) ? $relation['related_key'] : 'id';
		$query = $ci->db
					->where_in($related_key, $related_ids)
					->get("{$relation['related_table']}");
		if ($query->num_rows() > 0)
		{
			$class_name = ucfirst($relation['model']);
			$result = $query->result($class_name);
		} 
		else 
		{
			$result = array();
		}

		$data = array();

		foreach ($result as $row)
		{
			$data[$row->{$related_key}] = $row;
		}

		foreach ($objects as $object)
		{
			$alias = key($relation);
			$object->{$alias} = $data[$object->{$relation['with_key']}];
		}

		return $objects;
	}

	function hydrate_has_many($relation, $objects) {}

	function has_and_belongs_to_many($relation, $objects) {}

}