<?php
//##copyright##

$iaDb->setTable('ratings');

if (isset($iaCore->requestPath[0]))
{
	if ($iaCore->requestPath[0] == 'config')
	{
		$_GET['do'] = $iaCore->requestPath[0];
	}
}

if ($iaView->getRequestType() == iaView::REQUEST_JSON && isset($_GET['action']))
{
	if ('get' == $_GET['action'])
	{
		$start = isset($_GET['start']) ? (int)$_GET['start'] : 0;
		$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

		$out = array('data' => '', 'total' => 0);

		$where = array();

		if (isset($_GET['title']) && !empty($_GET['title']))
		{
			$where[] = "`item_title` LIKE :title";
			$values['title'] = '%'.$_GET['title'].'%';
		}

		if (isset($_GET['item']) && !empty($_GET['item']))
		{
			$where[] = "`item_type` = :item";
			$values['item'] = $_GET['item'];
		}

		if (isset($_GET['id']) && !empty($_GET['id']))
		{
			$where[] = "`obj_id` = :id";
			$values['id'] = (int)$_GET['id'];
		}

		if (empty($where))
		{
			$where[] = iaDb::EMPTY_CONDITION;
			$values = array();
		}

		$where = implode(' AND ', $where);
		$iaDb->bind($where, $values);

		$out['total'] = $iaDb->one(iaDb::STMT_COUNT_ROWS, $where);
		$out['data'] = $iaDb->all("*, 1 as `remove`", $where, $start, $limit);
	}

	if (empty($out['data']))
	{
		$out['data'] = '';
	}

	$iaView->assign($out);
}

if (isset($_POST['action']) && $iaView->getRequestType() == iaView::REQUEST_JSON)
{
	if ('remove' == $_POST['action'])
	{
		$out = array('msg' => 'Unknown error', 'error' => true);

		$ratings = $_POST['ids'];

		if (!is_array($ratings) || empty($ratings))
		{
			$out['msg'] = 'Wrong params';
			$out['error'] = true;
		}
		else
		{
			$ratings = array_map(array('iaSanitize', 'sql'), $ratings);
			$out['error'] = false;
		}

		if (!$out['error'])
		{
			if (is_array($ratings))
			{
				foreach($ratings as $rate)
				{
					$ids[] = (int)$rate;
				}

				$where = "`id` IN ('".join("','", $ids)."')";
			}
			else
			{
				$id = (int)$ratings;

				$where = "`id` = '{$id}'";
			}

			$iaDb->delete($where);
			$iaDb->resetTable();

			$out['msg'] = iaLanguage::get('rating_deleted');
			$out['error'] = false;
		}
	}

	$iaView->assign($out);
}
/*
 * ACTIONS
 */

if ($iaView->getRequestType() == iaView::REQUEST_HTML)
{
	iaBreadcrumb::add(iaLanguage::get('manage_ratings'), IA_ADMIN_URL . 'ratings/');

	if (isset($_GET['do']))
	{
		if ('config' == $_GET['do'])
		{
			$messages = array();
			$res = $iaDb->all('items', "`type` = 'package'", null, null, 'extras');
			$items = array('members');
			foreach ($res as $key => $val)
			{
				$package_items = unserialize($val['items']);
				$list = array();
				foreach($package_items as $package_item)
				{
					$list[] = $package_item['item'];
				}
				$items = array_merge($items, $list);
			}
			$active_items = unserialize($iaCore->get('ratings_item'));

			if (isset($_POST['action']))
			{
				switch($_POST['action'])
				{
					case 'save_texts':
						if (isset($_POST['rate']))
						{
							$iaCore->set('ratings_stars', serialize($_POST['rate']), true);
						}
						break;
				}

			}

			$list = array();
			foreach($items as $item)
			{
				$list[] = array(
					'value' => $item,
					'checked' => in_array($item, $active_items),
					'title' => iaLanguage::get($item, $item)
				);
			}

			$iaView->assign('ratings_texts', unserialize($iaCore->get('ratings_stars', 'a:0:{}')));
			$iaView->assign('items', $list);
			iaBreadcrumb::add(iaLanguage::get('ratings_config'), IA_SELF);
			$iaView->title(iaLanguage::get('ratings_config'));
		}
		$iaView->display();
	}
	else
	{
		$iaView->grid('_IA_URL_plugins/ratings/js/admin/ratings');
	}
}
$iaDb->resetTable();