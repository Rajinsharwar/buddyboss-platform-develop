<?php

add_filter ('bp_ps_add_fields', 'bp_ps_xprofile_setup');
function bp_ps_xprofile_setup ($fields)
{
	global $group, $field;

	$args = array ('hide_empty_fields' => false, 'member_type' => bp_get_member_types ());
	if (bp_has_profile ($args))
	{
		while (bp_profile_groups ())
		{
			bp_the_profile_group ();
			$group_name = str_replace ('&amp;', '&', stripslashes ($group->name));

			while (bp_profile_fields ())
			{
				bp_the_profile_field ();
				$f = new stdClass;

				$f->group = $group_name;
				$f->id = $field->id;
				$f->code = 'field_'. $field->id;
				$f->name = str_replace ('&amp;', '&', stripslashes ($field->name));
				$f->name = $f->name;
				$f->description = str_replace ('&amp;', '&', stripslashes ($field->description));
				$f->description = $f->description;
				$f->type = $field->type;

				$f->format = bp_ps_xprofile_format ($field->type, $field->id);
				$f->search = 'bp_ps_xprofile_search';
				$f->sort_directory = 'bp_ps_xprofile_sort_directory';
				$f->get_value = 'bp_ps_xprofile_get_value';

				$f->options = bp_ps_xprofile_options ($field->id);
				foreach ($f->options as $key => $label)
					$f->options[$key] = $label;

				if ($f->format == 'custom')
					do_action ('bp_ps_custom_field', $f);

				if ($f->format == 'set')
					unset ($f->sort_directory, $f->get_value);

				$fields[] = $f;
			}
		}
	}

	return $fields;
}

function bp_ps_xprofile_search ($f)
{   
    global $bp, $wpdb;
    
	$value = $f->value;
	$filter = $f->format. '_'.  ($f->filter == ''? 'is': $f->filter);
    
	$sql = array ('select' => '', 'where' => array ());
	$sql['select'] = "SELECT user_id FROM {$bp->profile->table_name_data}";
	$sql['where']['field_id'] = $wpdb->prepare ("field_id = %d", $f->id);

	switch ($filter)
	{
	case 'integer_range':
		if (isset ($value['min']))  $sql['where']['min'] = $wpdb->prepare ("value >= %d", $value['min']);
		if (isset ($value['max']))  $sql['where']['max'] = $wpdb->prepare ("value <= %d", $value['max']);
		break;

	case 'decimal_range':
		if (isset ($value['min']))  $sql['where']['min'] = $wpdb->prepare ("value >= %f", $value['min']);
		if (isset ($value['max']))  $sql['where']['max'] = $wpdb->prepare ("value <= %f", $value['max']);
		break;

	case 'date_date_range':
        $range_types = array( 'min', 'max' );
        foreach ( $range_types as $range_type ) {
            if ( isset( $value[ $range_type ]['year'] ) && !empty( $value[ $range_type ]['year'] ) ) {
                $year = $f->value[ $range_type ]['year'];
                $month  = !empty( $f->value[ $range_type ]['month'] ) ? $f->value[ $range_type ]['month'] : '00';
                $day    = !empty( $f->value[ $range_type ]['day'] ) ? $f->value[ $range_type ]['day'] : '00';
                $date = $year . '-' . $month . '-' . $day;
                
                $operator = 'min' == $range_type ? '>=' : '<=';
                
                $sql['where'][ $range_type ] = $wpdb->prepare ( "DATE(value) $operator %s", $date );
            }
        }
		break;

	case 'date_age_range':
		$day = date ('j');
		$month = date ('n');
		$year = date ('Y');

		if (isset ($value['max']))
		{
			$ymin = $year - $value['max'] - 1; 
			$sql['where']['age_min'] = $wpdb->prepare ("DATE(value) > %s", "$ymin-$month-$day");
		}
		if (isset ($value['min']))
		{
			$ymax = $year - $value['min'];
			$sql['where']['age_max'] = $wpdb->prepare ("DATE(value) <= %s", "$ymax-$month-$day");
		}
		break;

	case 'text_contains':
	case 'location_contains':
        if ( is_array( $value ) ) {
            $values = (array)$value;
            $parts = array ();
            foreach ( $values as $v ) {
                $v = str_replace ( '&', '&amp;', $v );
                $escaped = '%'. bp_ps_esc_like ( $v ). '%';
                $parts[] = $wpdb->prepare ( "value LIKE %s", $escaped);
            }
            $match = ' OR ';
            $sql['where'][$filter] = '('. implode ($match, $parts). ')';
        } else {
            $value = str_replace ('&', '&amp;', $value);
            $escaped = '%'. bp_ps_esc_like ($value). '%';
            $sql['where'][$filter] = $wpdb->prepare ("value LIKE %s", $escaped);
        }
		break;

	case 'text_like':
	case 'location_like':
		$value = str_replace ('&', '&amp;', $value);
		$value = str_replace ('\\\\%', '\\%', $value);
		$value = str_replace ('\\\\_', '\\_', $value);
		$sql['where'][$filter] = $wpdb->prepare ("value LIKE %s", $value);
		break;

	case 'text_is':
	case 'location_is':
		$value = str_replace ('&', '&amp;', $value);
		$sql['where'][$filter] = $wpdb->prepare ("value = %s", $value);
		break;

	case 'integer_is':
		$sql['where'][$filter] = $wpdb->prepare ("value = %d", $value);
		break;

	case 'decimal_is':
		$sql['where'][$filter] = $wpdb->prepare ("value = %f", $value);
		break;

	case 'date_is':
		$sql['where'][$filter] = $wpdb->prepare ("DATE(value) = %s", $value);
		break;

	case 'text_one_of':
		$values = (array)$value;
		$parts = array ();
		foreach ($values as $value)
		{
			$value = str_replace ('&', '&amp;', $value);
			$parts[] = $wpdb->prepare ("value = %s", $value);
		}
		$sql['where'][$filter] = '('. implode (' OR ', $parts). ')';
		break;

	case 'set_match_any':
	case 'set_match_all':
		$values = (array)$value;
		$parts = array ();
		foreach ($values as $value)
		{
			$value = str_replace ('&', '&amp;', $value);
			$escaped = '%:"'. bp_ps_esc_like ($value). '";%';
			$parts[] = $wpdb->prepare ("value LIKE %s", $escaped);
		}
		$match = ($filter == 'set_match_any')? ' OR ': ' AND ';
		$sql['where'][$filter] = '('. implode ($match, $parts). ')';
		break;

	default:
		return array ();
	}

	$sql = apply_filters ('bp_ps_field_sql', $sql, $f);
	$query = $sql['select']. ' WHERE '. implode (' AND ', $sql['where']);
    
	$results = $wpdb->get_col ($query);
	return $results;
}

function bp_ps_xprofile_sort_directory ($sql, $object, $f, $order)
{
	global $bp, $wpdb;

	$object->uid_name = 'user_id';
	$object->uid_table = $bp->profile->table_name_data;

	$sql['select'] = "SELECT u.user_id AS id FROM {$object->uid_table} u";
	$sql['where'] = str_replace ('u.ID', 'u.user_id', $sql['where']);
	$sql['where'][] = "u.user_id IN (SELECT ID FROM {$wpdb->users} WHERE user_status = 0)";
	$sql['where'][] = $wpdb->prepare ("u.field_id = %d", $f->id);
	$sql['orderby'] = "ORDER BY u.value";
	$sql['order'] = $order;

	return $sql;
}

function bp_ps_xprofile_get_value ($f)
{
	global $members_template;

	if ($members_template->current_member == 0)
	{
		$users = wp_list_pluck ($members_template->members, 'ID');
		BP_XProfile_ProfileData::get_value_byid ($f->id, $users);
	}

	$value = BP_XProfile_ProfileData::get_value_byid ($f->id, $members_template->member->ID);
	return stripslashes ($value);
}

function bp_ps_xprofile_format ($type, $field_id)
{
	$formats = array
	(
		'textbox'			=> array ('text', 'decimal'),
		'number'			=> array ('integer'),
		'telephone'			=> array ('text'),
		'url'				=> array ('text'),
		'textarea'			=> array ('text'),
		'selectbox'			=> array ('text', 'decimal'),
		'radio'				=> array ('text', 'decimal'),
		'multiselectbox'	=> array ('set'),
		'checkbox'			=> array ('set'),
		'datebox'			=> array ('date'),
	);

	if (!isset ($formats[$type]))  return 'custom';

	$formats = $formats[$type];
	$default = $formats[0];
	$format = apply_filters ('bp_ps_xprofile_format', $default, $field_id);

	return in_array ($format, $formats)? $format: $default;
}

function bp_ps_xprofile_options ($field_id)
{
	$field = new BP_XProfile_Field ($field_id);
	if (empty ($field->id))  return array ();

	$options = array ();
	$rows = $field->get_children ();
	if (is_array ($rows))
		foreach ($rows as $row)
			$options[stripslashes (trim ($row->name))] = stripslashes (trim ($row->name));

	return $options;
}

add_filter ('bp_ps_add_fields', 'bp_ps_anyfield_setup');
function bp_ps_anyfield_setup ($fields)
{
	$f = new stdClass;
	$f->group = __('Keyword', 'buddyboss');
	$f->code = 'field_any';
	$f->name = __('Search all fields', 'buddyboss');
	$f->description = __('Search every profile field', 'buddyboss');

	$f->format = 'text';
	$f->options = array ();
	$f->search = 'bp_ps_anyfield_search';

	$fields[] = $f;
	return $fields;
}

// Hook for registering a LearnDash course field in frontend and backend.
add_filter ('bp_ps_add_fields', 'bp_ps_learndash_course_setup');

/**
 * Function for registering a LearnDash course field in frontend and backend.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $fields
 *
 * @return array
 */
function bp_ps_learndash_course_setup ($fields) {

	// check is LearnDash plugin is activated or not.
	if(in_array('sfwd-lms/sfwd_lms.php', apply_filters('active_plugins', get_option('active_plugins')))){

		global $wpdb;

		$query = "SELECT DISTINCT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_status = %s ORDER BY menu_order";

		$courses_arr = $wpdb->get_col( $wpdb->prepare( $query, 'sfwd-courses', 'publish' ) );

		$courses = array();

		if ( $courses_arr ) :

			foreach ( $courses_arr as $course ) {
				$post = get_post( $course );
				$courses[ $post->ID ] = get_the_title( $post->ID );
			}

		endif;

		$f = new stdClass;
		$f->group = __('LearnDash', 'buddyboss');
		$f->id = 'learndash_courses';
		$f->code = 'field_learndash_courses';
		$f->name = __('Courses', 'buddyboss');
		$f->description = __('Courses', 'buddyboss');
		$f->type = 'selectbox';
		$f->format = bp_ps_xprofile_format('selectbox','learndash_courses');
		$f->options = $courses;
		$f->search = 'bp_ps_learndash_course_users_search';

		$fields[] = $f;

	}
	return $fields;
}

/**
 * Function for fetching all the users from selected course.
 *
 * @since BuddyBoss 1.0.0
 *
 * @param $f
 *
 * @return array
 */
function bp_ps_learndash_course_users_search( $f ) {

	// check for learndash plugin is activated or not.
	if(in_array('sfwd-lms/sfwd_lms.php', apply_filters('active_plugins', get_option('active_plugins')))) {


		$course_id = $f->value;
		if ( isset( $course_id ) && ! empty( $course_id ) ) {
			$course_users = learndash_get_users_for_course( $course_id, '', false );

			$course_users = $course_users->results;

			if ( isset( $course_users ) && ! empty( $course_users ) ) {
				return $course_users;
			} else {
				return array();
			}
		} else {
			return array();
		}
	}
}

function bp_ps_anyfield_search ($f)
{
	global $bp, $wpdb;

	$filter = $f->filter;
	$value = str_replace ('&', '&amp;', $f->value);

	$sql = array ('select' => '', 'where' => array ());
	$sql['select'] = "SELECT DISTINCT user_id FROM {$bp->profile->table_name_data}";

	switch ($filter)
	{
	case 'contains':
		$escaped = '%'. bp_ps_esc_like ($value). '%';
		$sql['where'][$filter] = $wpdb->prepare ("value LIKE %s", $escaped);
		break;

	case '':
		$sql['where'][$filter] = $wpdb->prepare ("value = %s", $value);
		break;

	case 'like':
		$value = str_replace ('\\\\%', '\\%', $value);
		$value = str_replace ('\\\\_', '\\_', $value);
		$sql['where'][$filter] = $wpdb->prepare ("value LIKE %s", $value);
		break;
	}

	$sql = apply_filters ('bp_ps_field_sql', $sql, $f);
	$query = $sql['select']. ' WHERE '. implode (' AND ', $sql['where']);

	$results = $wpdb->get_col ($query);
	return $results;
}
