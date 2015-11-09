<?php
//##copyright##

if (iaView::REQUEST_JSON == $iaView->getRequestType() && isset($_GET['star']))
{
	$iaSmarty = $iaCore->factory(iaCore::CORE, 'smarty');

	$out	= array('error' => false, 'msg' => array());
	$info	= array(
		'item_title' => isset($_GET['title']) && !empty($_GET['title']) ? iaSanitize::sql(iaSanitize::html($_GET['title'])) : 'none',
		'item_url' => isset($_GET['url']) ? iaSanitize::sql($_GET['url']) : IA_URL
	);
	$change	= array();
	$item	= isset($_GET['item']) ? $_GET['item'] : 'none';
	$id		= isset($_GET['id']) ? (int)$_GET['id'] : 0;
	$items	= explode(',', $iaCore->get('ratings_items_enabled'));
	$temp	= $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, "`id` = '$id'", $item);
	$addit	= array('date' => iaDb::FUNCTION_NOW);

	if (!$iaCore->get('listing_ratings_accounts', false) && !iaUsers::hasIdentity())
	{
		$out['error'] = true;
		$out['msg'] = iaLanguage::get('no_auth');
	}
	elseif(in_array($item, $items) && $id > 0 && $temp)
	{
		//$info['url'] = $iaSmarty->goToItem(array('itemtype' => $item, 'item' => $temp, 'noimage' => true));
		if ($item == 'accounts')
		{
			//$info['title'] = !empty($temp['fullname']) ? $temp['fullname'] : $temp['username'];
		}

		$iaDb->setTable('ratings');

		$current = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, "`obj_id` = '$id' AND `item_type` = '$item'");
		if (!$current)
		{
			$current = array(
				'obj_id'		=> $id,
				'item_type'		=> $item,
				'item_title'	=> $info['item_title'],
				'item_url'		=> $info['item_url'],
				'rate'			=> '0',
				'rate_num'		=> 0,
			);
			$current['id'] = $iaDb->insert($current, array('date' => 'NOW()'));
		}
		$iaDb->resetTable();

		$iaDb->setTable('ratings_click');

		$max_star	= $iaCore->get('ratings_max_star', 5);
		$star		= max(0, min($max_star, (int)$_GET['star']));
		$where		= array('id_session' => session_id(), 'rate_id' => $current['id']);

		if (iaUsers::hasIdentity())
		{
			$where['user'] = iaUsers::getIdentity()->id;
			$old_rate_where = "`user` = '{$where['user']}'";
			$rate_id = $iaDb->one("`id`", "`id_session` = '{$where['id_session']}' AND `rate_id` = '{$current['id']}'");
			if ($rate_id)
			{
				$iaDb->update(array('user' => $where['user'], 'id' => $rate_id));
			}
		}
		else
		{
			$old_rate_where = "`id_session` = '{$where['id_session']}'";
		}

		$old_rate = $iaDb->one("`rate`", $old_rate_where . " AND `rate_id` = '{$current['id']}'");
		$change['id'] = $current['id'];
		// if already rate
		if ($old_rate)
		{
			// delete my rate
			if ($star == 0)
			{
				$change['rate'] = ( $current['rate'] * $current['rate_num'] - $old_rate ) / ( $current['rate_num'] - 1 );
				$change['rate_num'] = $current['rate_num'] - 1;
				$iaDb->delete($old_rate_where);
			}
			// change my rate
			else
			{
				$change['rate'] = ( $current['rate'] * $current['rate_num'] - $old_rate + $star ) / $current['rate_num'];
				$iaDb->update(array('rate' => $star), $old_rate_where, $addit);
			}
		}
		// if not yet rate and rate is NULL
		elseif($star != 0)
		{
			$change['rate'] = ( $current['rate'] * $current['rate_num'] + $star ) / ( $current['rate_num'] + 1 );
			$change['rate_num'] = $current['rate_num'] + 1;
			$where['rate'] = $star;
			$iaDb->insert($where, $addit);
		}
		if (empty($change['rate']))
		{
			$change['rate'] = 0;
		}

		$iaDb->resetTable();

		$iaDb->update(array_merge($change, $info), null, array('date' => iaDb::FUNCTION_NOW), 'ratings');

		$out['msg'] = iaLanguage::get('changes_saved');
		if ($change['rate'] == 0)
		{
			$change['rate'] = iaLanguage::get('not_rated');
		}
		$out['rate'] = $change['rate'];
	}

	$iaView->assign($out);
}