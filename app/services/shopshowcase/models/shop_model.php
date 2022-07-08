<?php

class shop_model
{

	public $getBreadcrumbs = false;
	public $breadcrumbs = array();
	public $allGroups = false;
	public $allOptions = false;
	public $productsIdInGroup = false;

	public function init()
	{
		if ($this->getBreadcrumbs && empty($this->breadcrumbs)) {
			if (isset($_SESSION['alias']->breadcrumb_name))
				$this->breadcrumbs = $_SESSION['alias']->breadcrumbs;
			else {
				$where = array('alias' => $_SESSION['alias']->id, 'content' => 0);
				if ($_SESSION['language'])
					$where['language'] = $_SESSION['language'];
				if ($data = $this->db->getAllDataById('wl_ntkd', $where))
					$this->breadcrumbs = array($data->name => $_SESSION['alias']->alias);
			}
		}

		if ($_SESSION['option']->useGroups && empty($this->allGroups)) {
			$allGroups = $this->db->cache_get('allGroups');
			if ($allGroups === NULL) {
				$where = array();
				$where['wl_alias'] = $_SESSION['alias']->id;
				$this->db->select($this->table('_groups') . ' as g', '*', $where);

				$where_ntkd['alias'] = $_SESSION['alias']->id;
				$where_ntkd['content'] = "#-g.id";
				if ($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
				$this->db->join('wl_ntkd', "name, list", $where_ntkd);
				$this->db->order($_SESSION['option']->groupOrder);
				if ($list = $this->db->get('array')) {
					foreach ($list as $g) {
						$this->allGroups[$g->id] = clone $g;
					}
					unset($list);
				}
				$this->db->cache_add('allGroups', $this->allGroups);
			} else
				$this->allGroups = $allGroups;
		}
	}

	public function table($sufix = '', $useAliasTable = false)
	{
		if ($useAliasTable)
			return $_SESSION['service']->table . $sufix . $_SESSION['alias']->table;
		return $_SESSION['service']->table . $sufix;
	}

	public function routeURL($url = array(), &$type = null, $admin = false)
	{
		$this->init();
		$this->getBreadcrumbs = true;
		$_SESSION['alias']->breadcrumbs = array();
		if ($_SESSION['alias']->content < 0)
			if ($group = $this->getGroupByAlias(-$_SESSION['alias']->content, 0, 'id')) {
				$type = 'group';
				return $group;
			}

		$key = end($url);
		$keyName = 'alias';
		if ($_SESSION['alias']->content > 0) {
			$key = $_SESSION['alias']->content;
			$keyName = 'id';
		}
		if ($product = $this->getProduct($key, $keyName)) {
			$url = implode('/', $url);
			if ($url != $product->link) {
				$link = SITE_URL;
				if (!empty($_SESSION['user']) && !empty($_SESSION['user']->id) && ($_SESSION['user']->admin || $_SESSION['user']->manager))
					$this->db->sitemap_update($product->id, 'link', $product->link);
				else
					$this->db->sitemap_redirect($product->link);
				if ($admin)
					$link .= 'admin/';

				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ' . $link . $product->link);
				exit();
			}

			$type = 'product';
			return $product;
		}

		if ($_SESSION['option']->useGroups && !empty($this->allGroups)) {
			$gId = false;
			$parent = 0;
			array_shift($url);
			foreach ($url as $uri) {
				$gId = false;
				foreach ($this->allGroups as $g) {
					if ($g->alias == $uri && $g->parent == $parent) {
						$parent = $gId = $g->id;
						break;
					}
				}
			}
			if ($gId)
				if ($group = $this->getGroupByAlias($gId, 0, 'id')) {
					$type = 'group';
					return $group;
				}
		}

		return false;
	}

	public function getProducts($Group = -1, $noInclude = 0, $active = true, $getProductOptions = false)
	{
		$paginate_flag = true;
		$_SESSION['option']->paginator_total = $_SESSION['option']->paginator_total_active = 0;
		$where = array('wl_alias' => $_SESSION['alias']->id);
		if (is_bool($active))
			$active = $active ? 1 : 0;
		if (is_numeric($active) && $active != 0) {
			if ($_SESSION['option']->useGroups == 1) {
				if ($_SESSION['option']->ProductMultiGroup == 0)
					$where['active'] = $active;
			} else
				$where['active'] = $active;
		}

		if ($_SESSION['option']->ProductUseArticle > 0 && is_string($Group) && $Group[0] == '%')
			$where['article'] = $Group;
		elseif ($_SESSION['option']->useGroups > 0) {
			if (is_array($Group) && isset($Group[key($Group)]->id)) {
				$list = array();
				foreach ($Group as $g) {
					$list[] = $g->id;
				}
				$Group = $list;
				unset($list);
			}
			if ($active <= 0 && $Group >= 0 && !$this->data->get('name')) {
				if ($_SESSION['option']->ProductMultiGroup)
					$where['#pg.group'] = $Group;
				else
					$where['group'] = $Group;
			} else {
				if (is_array($Group) && in_array(0, $Group) || is_numeric($Group) && $Group <= 0) {
					// if($_SESSION['option']->ProductMultiGroup)
					// {
					// 	$order = explode(' ', trim($_SESSION['option']->productOrder));
					// 	if($order[0] == 'position')
					// 		$order = 'ORDER BY '.trim($_SESSION['option']->productOrder);
					// 	else
					// 		$order = '';

					// 	$getOk = true;
					// 	if(count($_GET) > 1)
					// 	{
					// 		$getOk = false;
					// 		if(count($_GET) == 2 && isset($_GET['request']) && isset($_GET['page']))
					// 			$getOk = true;
					// 	}
					// 	if($getOk && isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0)
					// 	{
					// 		$start = 0;
					// 		if(isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
					// 			$_SESSION['option']->paginator_per_page = $_GET['per_page'];
					// 		if(isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
					// 			$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
					// 		$order .= ' LIMIT '.$start.', '.$_SESSION['option']->paginator_per_page;
					// 	}
					// 	$pgNoInclude = ($active > 0) ? '`active` = 1' : '';
					// 	$pgNoInclude .= ($noInclude > 0) ? ' AND `product` != '.$noInclude : '';
					// 	if($pgNoInclude != '')
					// 		$pgNoInclude = 'WHERE '.$pgNoInclude;
					// 	if($products = $this->db->getQuery("SELECT `product` FROM `{$this->table('_product_group')}` {$pgNoInclude} GROUP BY `product` ".$order, 'array'))
					// 	{
					// 		$where['id'] = array();
					// 		foreach ($products as $product) {
					// 			array_push($where['id'], $product->product);
					// 		}
					// 		// $this->productsIdInGroup = $where['id'];
					// 	}
					// 	else
					// 		return null;
					// }
					// else
					if ($noInclude > 0)
						$where['id'] = '!' . $noInclude;
				} else {
					$endGroups = $this->getEndGroups($Group);
					if (!empty($endGroups)) {
						if ($_SESSION['option']->ProductMultiGroup == 0) {
							$where['group'] = $endGroups;
							if ($noInclude > 0)
								$where['id'] = '!' . $noInclude;
						} else {
							$wherePG = array('active' => 1);
							if ($noInclude > 0)
								$wherePG['product'] = '!' . $noInclude;
							$wherePG['group'] = $endGroups;
							$this->db->select($this->table('_product_group') . ' as pg', 'product', $wherePG);
							$this->db->group('product');

							$order = explode(' ', trim($_SESSION['option']->productOrder));
							if ($order[0] == 'position')
								$this->db->order(trim($_SESSION['option']->productOrder));

							$start = -1;
							$filterOk = true;
							if (count($_GET) > 1) {
								$filterOk = false;
								if (count($_GET) == 2 && (isset($_GET['request']) && isset($_GET['page']) && isset($_GET['sale']) && isset($_GET['availability'])))
									$filterOk = true;
							}
							if ($filterOk && isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0) {
								$start = 0;
								if (isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
									$_SESSION['option']->paginator_per_page = $_GET['per_page'];
								if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
									$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
								$this->db->limit($start, $_SESSION['option']->paginator_per_page);
								$paginate_flag = false;
							}

							if ($products = $this->db->get('array', false)) {
								if ($start >= 0 && $_SESSION['option']->paginator_per_page >= count($products)) {
									$filterOk = false;
									$_SESSION['option']->paginator_total = $this->db->get('count');
								} else {
									if (count($_GET) > 1 || !isset($_GET['request']))
										$filterOk = false;
									$_SESSION['option']->paginator_total = count($products);
								}
								$this->db->clear();
								$where['id'] = array();
								$this->productsIdInGroup = array();
								foreach ($products as $product) {
									array_push($where['id'], $product->product);
									if ($filterOk)
										$this->productsIdInGroup[] = $product->product;
								}
							} else
								return null;
						}
					} else
						return false;
				}
			}
		} elseif ($noInclude > 0)
			$where['id'] = '!' . $noInclude;

		if (isset($_GET['sale']) && $_GET['sale'] == 1) {
			$time = time();
			$where['&'] = "p.old_price > p.price OR (p.promo > 0 AND pm.status = 1 AND pm.from < {$time} AND pm.to >= {$time})";
		}
		if (isset($_GET['price_min']) && is_numeric($_GET['price_min']) && $_GET['price_min'] >= 1) {
			$price_min = $this->data->get('price_min');
			// if(isset($_SESSION['option']->currency) &&  $_SESSION['option']->currency)
			//       	$price_min /= $_SESSION['option']->currency;
			$where['price'] = '>=' . $price_min;
		}
		if (isset($_GET['price_max']) && is_numeric($_GET['price_max']) && $_GET['price_max'] > 1) {
			$price_max = $this->data->get('price_max');
			// if(isset($_SESSION['option']->currency) && $_SESSION['option']->currency)
			//       	$price_max /= $_SESSION['option']->currency;
			$where['price'] = '<=' . $price_max;
		}

		if (!empty($_GET['availability']) && is_numeric($_GET['availability'])) {
			if ($_SESSION['option']->useAvailability) {
				$where['availability'] = '>0';
				if (empty($where['price']))
					$where['price'] = '>0';
			} else
				$where['availability'] = $this->data->get('availability');
		}

		if (!empty($_GET['author_add']) && is_numeric($_GET['author_add']) && $_GET['author_add'] > 0)
			$where['author_add'] = $_GET['author_add'];

		if ($active > 0 && $_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0 && isset($where['group']))
			$where['active'] = 1;

		if (count($_GET) > 1 || empty($_GET['request'])) {
			if ($_SESSION['option']->ProductUseArticle > 0 && !empty($_GET['article']))
				$where['article'] = '%' . $this->prepareArticleKey($this->data->get('article'));

			if ($name = $this->data->get('name')) {
				if ($_SESSION['option']->searchHistory)
					if (empty($_SESSION['user']->id) || (empty($_SESSION['user']->admin) && empty($_SESSION['user']->manager)))
						$this->searchHistory($name);

				$content = '>0';
				if (!empty($where['id']))
					$content = $where['id'];
				if ($products = $this->db->getAllDataByFieldInArray('wl_ntkd', array('alias' => $_SESSION['alias']->id, 'content' => $content, 'name' => '~' . $name))) {
					if (!isset($where['id'])) {
						$where['id'] = array();
						foreach ($products as $p) {
							array_push($where['id'], $p->content);
						}
					} else {
						$ids = $where['id'];
						$where['id'] = array();
						foreach ($products as $p) {
							if (in_array($p->content, $ids))
								array_push($where['id'], $p->content);
						}
					}
					if (empty($where['id']))
						return false;

					if ($products = $this->db->select($this->table('_products') . ' as p', 'id', $where)->get('array')) {
						$this->productsIdInGroup = array();
						foreach ($products as $product) {
							$this->productsIdInGroup[] = $product->id;
						}
						$where['id'] = $this->productsIdInGroup;
					} else
						return false;
				} else
					return false;
			}

			if (empty($where['id'])) {
				$list = $this->db->select($this->table('_products') . ' as p', 'id', $where)
													->join($this->table('_promo') . ' as pm', '', '#p.promo')
													->get('array');
				if (empty($list))
					return false;

				$where['id'] = [];
				foreach ($list as $p) {
					array_push($where['id'], $p->id);
				}
			}

			foreach ($_GET as $key => $value) {
				if (in_array($key, ['request', 'page', 'per_page', 'article', 'name', 'sale', 'price_min', 'price_max', 'availability', 'author_add', 'sort']))
					continue;

				if ($option = $this->db->getAllDataById($this->table('_options'), array('wl_alias' => $_SESSION['alias']->id, 'alias' => $key, 'filter' => 1))) {
					$list_where['option'] = $option->id;

					if (is_array($_GET[$key])) {
						if (!empty($where['id']))
							$list_where['product'] = $where['id'];
						$where['id'] = array();
						foreach ($_GET[$key] as $value)
							if (is_numeric($value)) {
								if ($option->type == 8 || $option->type == 12) //checkbox || checkbox-select2
								{
									$list_where['value'] = '%' . $value;
									if ($list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $list_where))
										foreach ($list as $p) {
											$p->value = explode(',', $p->value);
											if (in_array($value, $p->value))
												array_push($where['id'], $p->product);
										}
								} else {
									$list_where['value'] = $value;
									if ($list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $list_where))
										foreach ($list as $p) {
											array_push($where['id'], $p->product);
										}
								}
							}
					} else if (is_numeric($_GET[$key]) && $_GET[$key] > 0) {
						$list_where['value'] = $_GET[$key];
						if (!empty($where['id']))
							$list_where['product'] = $where['id'];
						$where['id'] = array();
						if ($list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $list_where))
							foreach ($list as $p) {
								array_push($where['id'], $p->product);
							}
					} else
						continue;
					if (empty($where['id']))
						return false;
				}
			}
		}
		
		$this->db->select($this->table('_products') . ' as p', '*', $where)
						->join($this->table('_promo') . ' as pm', 'from as promo_from, to as promo_to, percent', '#p.promo');

		if ($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup && !is_array($Group))
			$this->db->join($this->table('_product_group') . ' as pg', 'id as position_id, position, active', array('group' => $Group, 'product' => '#p.id'));

		// if(!$active)
		$this->db->join('wl_users', 'name as user_name', '#p.author_add');

		if ($_SESSION['option']->useAvailability == 0) {
			$this->db->join($_SESSION['service']->table . '_availability', 'color as availability_color', '#p.availability');

			$where_availability_name['availability'] = '#p.availability';
			if ($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
			$this->db->join($_SESSION['service']->table . '_availability_name', 'name as availability_name', $where_availability_name);
		}

		if ($_SESSION['option']->useMarkUp > 0)
			$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));
		$this->db->join($this->table('_promo'), 'percent as promo_percent, from as promo_from, to as promo_to', ['id' => '#p.promo', 'status' => 1]);

		if ($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0) {
			$where_gn['alias'] = $_SESSION['alias']->id;
			$where_gn['content'] = "#-p.group";
			if ($_SESSION['language']) $where_gn['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as gn', 'name as group_name', $where_gn);
			$this->db->join($this->table('_groups') . ' as g', 'active as group_active', '#p.group');
		}

		$where_ntkd['alias'] = $_SESSION['alias']->id;
		$where_ntkd['content'] = "#p.id";
		if ($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
		$this->db->join('wl_ntkd as n', 'name, text, list', $where_ntkd);

		if (isset($_GET['sort'])) {
			switch ($this->data->get('sort')) {
				case 'price_up':
					$this->db->order('price DESC');
					break;
				case 'price_down':
					$this->db->order('price ASC');
					break;
				case 'article':
					$this->db->order('article ASC');
					break;
				case 'article_desc':
					$this->db->order('article DESC');
					break;
				case 'name':
					$this->db->order('name ASC', 'n');
					break;
				case 'name_desc':
					$this->db->order('name DESC', 'n');
					break;
				case 'active_on':
					if ($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup)
						$this->db->order('active DESC', 'pg');
					else
						$this->db->order('active DESC');
					break;
				case 'active_off':
					if ($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup)
						$this->db->order('active ASC', 'pg');
					else
						$this->db->order('active ASC');
					break;
				default:
					$this->db->order($_SESSION['option']->productOrder);
					break;
			}
		} else if ($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup && !is_array($Group))
			$this->db->order($_SESSION['option']->productOrder, 'pg');
		else {
			$order = explode(' ', $_SESSION['option']->productOrder);
			if ($order[0] == 'name')
				$this->db->order($_SESSION['option']->productOrder, 'n');
			else
				$this->db->order($_SESSION['option']->productOrder);
		}

		if (isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0 && $paginate_flag) {
			$start = 0;
			if (isset($_GET['per_page']) && is_numeric($_GET['per_page']) && $_GET['per_page'] > 0)
				$_SESSION['option']->paginator_per_page = $_GET['per_page'];
			if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
				$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
			$this->db->limit($start, $_SESSION['option']->paginator_per_page);
		}

		$products = $set_paginator_total = false;
		if ($_SESSION['option']->useAvailability && empty($_GET['availability'])) {
			$set_paginator_total = true;
			$_SESSION['option']->paginator_total = $this->db->get('count', false);
			if ($_SESSION['option']->paginator_total) {
				$where['#p.availability'] = '>0';
				$this->db->select($this->table('_products') . ' as p', '*', $where, 'id', false);
				if ($products_availability = $this->db->get('array', false)) {
					$products = $products_availability;
					$count_products_availability = count($products_availability);
					if ($count_products_availability < $_SESSION['option']->paginator_per_page) {
						$where['#p.availability'] = 0;
						$paginator_per_page = $_SESSION['option']->paginator_per_page - $count_products_availability;
						$this->db->select($this->table('_products') . ' as p', '*', $where, 'id', false);
						$this->db->limit(0, $paginator_per_page);
						if ($products_no_availability = $this->db->get('array', false))
							$products = array_merge($products_availability, $products_no_availability);
					}
				} else {
					$count_products_availability = $this->db->get('count', false);
					$where['#p.availability'] = 0;
					$this->db->select($this->table('_products') . ' as p', '*', $where, 'id', false);
					$this->db->limit($start - $count_products_availability, $_SESSION['option']->paginator_per_page);
					$products = $this->db->get('array', false);
				}
			}
		} else
			$products = $this->db->get('array', false);

		if ($products) {
			if (!$set_paginator_total && (empty($_SESSION['option']->paginator_total) || count($_GET) > 1)) {
				if (count($products) < $_SESSION['option']->paginator_per_page && empty($_GET['page']))
					$_SESSION['option']->paginator_total = count($products);
				else {
					$_SESSION['option']->paginator_total = $this->db->get('count');

					if ($active <= 0) {
						$wherePG = array('active' => 1, 'group' => $Group);
						if ($_SESSION['option']->useGroups && $_SESSION['option']->ProductMultiGroup)
							$_SESSION['option']->paginator_total_active = $this->db->getCount($this->table('_product_group'), $wherePG);
						else
							$_SESSION['option']->paginator_total_active = $this->db->getCount($this->table('_products'), $wherePG);
					}
				}
			}
			$this->db->clear();

			if ($_SESSION['option']->useGroups && empty($this->allGroups))
				$this->init();

			$sizes = $this->db->getAliasImageSizes();

			$products_ids = $products_photos = $main_options = $main_options_Alias = $main_options_UseSubOptions = $product_group = array();
			foreach ($products as $product)
				$products_ids[] = $product->id;
			if ($photos = $this->getProductPhoto($products_ids)) {
				foreach ($photos as $photo) {
					$products_photos[$photo->content] = clone $photo;
				}
				unset($photos);
			}
			if (!$getProductOptions) {
				$this->db->select($this->table('_options') . ' as o', 'id, alias', array('wl_alias' => $_SESSION['alias']->id, 'main' => 1))
					->join('wl_input_types', 'options', '#o.type');
				if ($mainOptions = $this->db->get('array')) {
					$ids_simple = $ids_select = $mainOptionsAlias = array();
					foreach ($mainOptions as $o) {
						$ids[] = $o->id;
						$mainOptionsAlias[$o->id] = $o->alias;
						$o->alias = explode('-', $o->alias);
						if ($o->alias[0] == $o->id)
							array_shift($o->alias);
						$main_options_Alias[$o->id] = implode('_', $o->alias);
						if ($o->options) {
							$ids_select[] = $o->id;
						} else {
							$ids_simple[] = $o->id;
						}
					}

					if (!empty($ids_select)) {
						$where = array('option' => '#po.value');
						if ($_SESSION['language'])
							$where['language'] = $_SESSION['language'];
						$options = $this->db->select($this->table('_product_options') . ' as po', '`option`, product, value', array('product' => $products_ids, 'option' => $ids_select))
							->join($this->table('_options') . ' as o', 'photo', '#po.value')
							->join($this->table('_options_name'), 'name', $where)
							->get('array');
						if ($options)
							foreach ($options as $opt) {
								if (!empty($opt->name)) {
									if (strpos($opt->value, ',')) {
										$where = array('option' => '#o.id');
										if ($_SESSION['language'])
											$where['language'] = $_SESSION['language'];
										$main_options[$opt->product][$opt->option] = $this->db->select($this->table('_options') . ' as o', 'id, photo, position', ['id' => explode(',', $opt->value)])
											->join($this->table('_options_name') . ' as n', 'name', $where)
											->order('position')
											->get('array');
										if ($main_options[$opt->product][$opt->option])
											foreach ($main_options[$opt->product][$opt->option] as &$subOption) {
												if (!empty($subOption->photo))
													$subOption->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $mainOptionsAlias[$opt->option] . '/' . $subOption->photo;
											}
									} else {
										$main_options[$opt->product][$opt->option] = new stdClass();
										$main_options[$opt->product][$opt->option]->id = $opt->value;
										$main_options[$opt->product][$opt->option]->name = $opt->name;
										$main_options[$opt->product][$opt->option]->photo = '';
										if (!empty($opt->photo))
											$main_options[$opt->product][$opt->option]->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $mainOptionsAlias[$opt->option] . '/' . $opt->photo;
									}
								}
							}
						unset($options);
					}

					if (!empty($ids_simple)) {
						$where = array('product' => $products_ids, 'option' => $ids_simple);
						if ($_SESSION['language']) {
							$where['language'] = ['', $_SESSION['language']];
						}
						if ($options = $this->db->select($this->table('_product_options') . ' as po', '`option`, product, value, language', $where)->get('array'))
							foreach ($options as $opt) {
								$main_options[$opt->product][$opt->option] = $opt->value;
							}
						unset($options);
					}
				}
			}

			$link = $_SESSION['alias']->alias . '/';
			$parents = NULL;
			$parents_ids = [];
			$parents_for_group = 0;
			if ($_SESSION['option']->useGroups > 0) {
				if ($_SESSION['option']->ProductMultiGroup == 0 && $products[0]->group > 0) {
					$parents_for_group = $products[0]->group;
					$parents = $this->makeParents($products[0]->group, array());
					foreach ($parents as $parent) {
						$parents_ids[] = $parent->id;
						$link .= $parent->alias . '/';
					}
				} else if ($_SESSION['option']->ProductMultiGroup == 1) {

					if ($active > 0) {
						$this->db->select($this->table('_product_group') . ' as pg', 'product, active, position', array('product' => $products_ids, 'active' => 1));
						$this->db->join($this->table('_groups'), 'id, alias, parent', array('id' => '#pg.group', 'active' => 1));
					} else {
						$this->db->select($this->table('_product_group') . ' as pg', 'product, active, position', array('product' => $products_ids));
						$this->db->join($this->table('_groups'), 'id, alias, parent', '#pg.group');
					}
					$where_ntkd['content'] = "#-pg.group";
					$this->db->join('wl_ntkd', 'name', $where_ntkd);

					if ($list = $this->db->get('array'))
						foreach ($list as $row) {
							if (!isset($product_group[$row->product]))
								$product_group[$row->product] = array();
							$product_group[$row->product][] = $row;
						}
				}
			}

			$time = time();
			foreach ($products as $product) {
				if ($_SESSION['option']->paginator_total <= $_SESSION['option']->paginator_per_page) {
					if ($product->active)
						$_SESSION['option']->paginator_total_active++;
				}

				if ($_SESSION['option']->ProductUseArticle && mb_strlen($product->name) > mb_strlen($product->article)) {
					$name = explode(' ', $product->name);
					$last_name = array_pop($name);
					if ($last_name == $product->article || $last_name == $product->article_show)
						$product->name = implode(' ', $name);
				}

				$this->__setBaseProductPrice($product, NULL, $time);

				if ($parents_for_group != $product->group) {
					$link = $_SESSION['alias']->alias . '/';
					$parents_ids = [];
					$parents_for_group = $product->group;
					$parents = $this->makeParents($product->group, array());
					foreach ($parents as $parent) {
						$parents_ids[] = $parent->id;
						$link .= $parent->alias . '/';
					}
				}
				$product->link = $link . $product->alias;
				$product->parents = $parents;

				if ($getProductOptions)
					$product->options = $this->getProductOptions($product, $parents_ids);
				elseif (isset($main_options[$product->id]))
					foreach ($main_options[$product->id] as $opt_id => $value) {
						$key = $main_options_Alias[$opt_id];
						$product->$key = $value;
					}
				if ($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 0 && $products[0]->group > 0) {
					$product->group_link = $link;
					if (substr($product->group_link, -1) == '/')
						$product->group_link = substr($product->group_link, 0, -1);
				}

				$product->photo = null;
				if (isset($products_photos[$product->id])) {
					$photo = $products_photos[$product->id];
					if ($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix . '_photo';
							$product->$resize_name = $_SESSION['option']->folder . '/' . $product->id . '/' . $resize->prefix . '_' . $photo->file_name;
						}
					$product->photo = $_SESSION['option']->folder . '/' . $product->id . '/' . $photo->file_name;
				}

				if ($_SESSION['option']->useGroups > 0 && $_SESSION['option']->ProductMultiGroup == 1) {
					$product->group = array();
					if (!empty($product_group[$product->id]))
						foreach ($product_group[$product->id] as $g) {
							if ($g->parent > 0)
								$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($g->parent, $g->alias);
							else
								$g->link = $_SESSION['alias']->alias . '/' . $g->alias;
							$product->group[] = $g;
						}
				}
			}

			return $products;
		}
		$this->db->clear();
		return false;
	}

	public function getProductsCountInGroup($group_id = 0)
	{
		$wherePG = array('active' => 1);
		if ($group_id > 0)
			$wherePG['group'] = $this->getEndGroups($group_id);

		if ($_SESSION['option']->ProductMultiGroup) {
			$this->db->select($this->table('_product_group') . ' as pg', 'product', $wherePG);
			$this->db->group('product');
			return $this->db->get('count');
		} else
			return $this->db->getCount($this->table('_products'), $wherePG);
	}

	public function getProduct($alias, $key = 'alias', $all_info = true)
	{
		$time = time();
		$cache_key = 'product/' . $this->db->getCacheContentKey('product_', $alias, 2);
		$cache_key .= ($all_info) ? '-all_info' : '';
		if (!isset($_GET['edit']) && $key == 'id' && $product = $this->db->cache_get($cache_key))
			if ($product !== NULL) {
				$this->db->select($this->table('_products') . ' as p', 'price, old_price, currency, promo, availability', $product->id)
					->join($this->table('_promo'), 'percent as promo_percent, from as promo_from, to as promo_to', ['id' => '#p.promo', 'status' => 1]);
				if ($_SESSION['option']->useMarkUp > 0)
					$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));
				$row = $this->db->get();

				$this->__setBaseProductPrice($product, $row, $time);

				if ($this->getBreadcrumbs)
					$this->breadcrumbs = $product->breadcrumbs;

				return $product;
			}

		$this->db->select($this->table('_products') . ' as p', '*', array('wl_alias' => $_SESSION['alias']->id, $key => $alias));
		$this->db->join($this->table('_promo'), 'percent as promo_percent, from as promo_from, to as promo_to', ['id' => '#p.promo', 'status' => 1]);
		if ($_SESSION['option']->useMarkUp > 0)
			$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));

		if ($all_info) {
			$this->db->join('wl_users as aa', 'name as author_add_name, alias as author_add_alias, email as author_add_email', '#p.author_add');
			$this->db->join('wl_users as e', 'name as author_edit_name, alias as author_edit_alias, email as author_edit_email', '#p.author_edit');

			if ($_SESSION['option']->useAvailability == 0) {
				$this->db->join($_SESSION['service']->table . '_availability', 'color as availability_color', '#p.availability');

				$where_availability_name['availability'] = '#p.availability';
				if ($_SESSION['language']) $where_availability_name['language'] = $_SESSION['language'];
				$this->db->join($_SESSION['service']->table . '_availability_name', 'name as availability_name', $where_availability_name);
			}

			$where_ntkd['alias'] = $_SESSION['alias']->id;
			$where_ntkd['content'] = "#p.id";
			if ($_SESSION['language']) $where_ntkd['language'] = $_SESSION['language'];
			$this->db->join('wl_ntkd as n', 'name, list, text', $where_ntkd);
		}

		$product = $this->db->get();
		if (is_array($product)) {
			foreach ($product as $i => $p2) {
				if ($i > 0)
					$this->db->updateRow($this->table('_products'), ['alias' => $p2->alias . '--' . $p2->id], $p2->id);
			}
			$product = $product[0];
		}

		if (is_object($product)) {
			$cache_key = 'product/' . $this->db->getCacheContentKey('product_', $product->id, 2);
			$cache_key .= ($all_info) ? '-all_info' : '';

			if (!isset($_GET['edit'])) {
				$product_cache = $this->db->cache_get($cache_key);
				if ($product_cache !== NULL) {
					$this->__setBaseProductPrice($product_cache, $product, $time);

					if ($this->getBreadcrumbs)
						$this->breadcrumbs = $product_cache->breadcrumbs;

					return $product_cache;
				}

				$this->__setBaseProductPrice($product, NULL, $time);
			}

			$product->link = $_SESSION['alias']->alias . '/' . $product->alias;

			if ($_SESSION['option']->ProductUseArticle && mb_strlen($product->name) > mb_strlen($product->article)) {
				$name = explode(' ', $product->name);
				$name_last = array_pop($name);
				if ($name_last == $product->article || $name_last == $product->article_show)
					$product->name = implode(' ', $name);
			}
			$product->parents = array();
			if (!$this->getBreadcrumbs)
				$product->breadcrumbs = [];

			if ($_SESSION['option']->useGroups > 0) {
				if (empty($this->allGroups))
					$this->init();
				if (!empty($this->allGroups)) {
					if ($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0) {
						$product->group_name = $this->allGroups[$product->group]->name ?? '';
						$product->parents = $this->makeParents($product->group, $product->parents);
						$link = $_SESSION['alias']->alias . '/';
						foreach ($product->parents as $parent) {
							$link .= $parent->alias;
							if ($this->getBreadcrumbs)
								$this->breadcrumbs[$parent->name] = $link;
							else
								$product->breadcrumbs[$parent->name] = $link;
							$link .= '/';
						}
						$product->group_link = substr($link, 0, -1);
						$product->link = $link . $product->alias;
					} elseif ($_SESSION['option']->ProductMultiGroup == 1) {
						$product->group = array();

						$this->db->select($this->table('_product_group') . ' as pg', 'active', $product->id, 'product');
						$this->db->join($this->table('_groups'), 'id, alias, parent', array('id' => '#pg.group', 'active' => 1));
						$where_ntkd['content'] = "#-pg.group";
						$this->db->join('wl_ntkd', 'name', $where_ntkd);
						unset($where_ntkd['language']);
						$where_ntkd['position'] = 1;
						$where_ntkd['section_id'] = 0;
						$this->db->join('wl_images', 'file_name', $where_ntkd);
						if ($product->group = $this->db->get('array')) {
							$setBreadcrumbs = true;
							foreach ($product->group as $g) {
								if ($g->active)
									$product->active = $g->active;
								if ($g->parent > 0)
									$g->link = $_SESSION['alias']->alias . '/' . $this->makeLink($g->parent, $g->alias);
								else
									$g->link = $_SESSION['alias']->alias . '/' . $g->alias;
								$g->parents = array();
								if ($this->getBreadcrumbs && isset($_SERVER['HTTP_REFERER']) && $g->link == str_replace(SITE_URL, '', $_SERVER['HTTP_REFERER'])) {
									$setBreadcrumbs = false;
									$parents = $this->makeParents($g->id, $product->parents);
									$link = $_SESSION['alias']->alias;
									foreach ($parents as $parent) {
										$link .= '/' . $parent->alias;
										$g->parents[$parent->name] = $link;
										$this->breadcrumbs[$parent->name] = $link;
									}
								}
							}
							if ($this->getBreadcrumbs && $setBreadcrumbs) {
								$parents = $this->makeParents($product->group[0]->id, $product->parents);
								$link = $_SESSION['alias']->alias . '/';
								foreach ($parents as $parent) {
									$link .= $parent->alias . '/';
									$this->breadcrumbs[$parent->name] = $link;
								}
							}
						}
					}
				}
			}
			if ($all_info) {
				$parents_ids = $product->similarProducts = $main_options = $main_options_Alias = $main_options_UseSubOptions = array();
				if ($product->parents)
					foreach ($product->parents as $pid) {
						if (is_object($pid) && isset($pid->id))
							$parents_ids[] = $pid->id;
						elseif (is_numeric($pid) && $pid > 0)
							$parents_ids[] = $pid;
					}

				$this->db->select($this->table('_options') . ' as o', 'id, alias', array('wl_alias' => $_SESSION['alias']->id, 'main' => 1))
					->join('wl_input_types', 'options', '#o.type');
				if ($mainOptions = $this->db->get('array')) {
					$ids = array();
					foreach ($mainOptions as $o) {
						$ids[] = $o->id;
						$o->alias = explode('-', $o->alias);
						if ($o->alias[0] == $o->id)
							array_shift($o->alias);
						$alias = $main_options_Alias[$o->id] = implode('_', $o->alias);
						$main_options_UseSubOptions[$o->id] = $o->options;
						$product->$alias = '';
					}
				}

				if ($product->options = $this->getProductOptions($product, $parents_ids))
					foreach ($product->options as $option) {
						if ($option->main && isset($main_options_Alias[$option->id]) && !empty($option->value)) {
							$key = $main_options_Alias[$option->id];
							if (is_array($option->value)) {
								$names = [];
								foreach ($option->value as $value) {
									$names[] = $value->name;
								}
								$product->$key = implode(', ', $names);
							} else if (is_object($option->value))
								$product->$key = $option->value->name;
							else if (is_string($option->value))
								$product->$key = $option->value;
							if (!empty($product->$key) && !empty($option->sufix))
								$product->$key .= $option->sufix;
						}
					}

				$product->photo = null;
				$sizes = $this->db->getAliasImageSizes();
				// if(!$this->getBreadcrumbs)
				if ($photo = $this->getProductPhoto($product->id)) {
					if ($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix . '_photo';
							$product->$resize_name = $_SESSION['option']->folder . '/' . $product->id . '/' . $resize->prefix . '_' . $photo->file_name;
						}
					$product->photo = $_SESSION['option']->folder . '/' . $product->id . '/' . $photo->file_name;
				}

				if ($getSimilars = $this->db->getAllDataByFieldInArray($this->table('_products_similar'), array('product' => $product->id))) {
					$similarGroups = [];
					foreach ($getSimilars as $similar) {
						if (!in_array($similar->group, $similarGroups)) {
							$similarGroups[] = $similar->group;
						}
					}
					$this->db->select($this->table('_products_similar') . ' as s', 'id as similar_id, folder as similarFolder', array('group' => $similarGroups, 'product' => '!' . $product->id));
					$this->db->join($this->table('_products') . ' as p', 'id, alias, article, article_show, `group`, price, old_price, currency, availability', '#s.product');
					$where_ntkd['content'] = '#p.id';
					unset($where_ntkd['position']);
					$this->db->join('wl_ntkd', 'name', $where_ntkd);
					$this->db->order('folder');
					if ($_SESSION['option']->useMarkUp > 0)
						$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));
					if (!empty($main_options_Alias))
						foreach ($main_options_Alias as $option_id => $option_key) {
							if ($main_options_UseSubOptions[$option_id]) // use_options
							{
								$where = array('option' => '#po_' . $option_key . '.value');
								if ($_SESSION['language'])
									$where['language'] = $_SESSION['language'];
								$this->db->join($this->table('_product_options') . ' as po_' . $option_key, '', array('product' => '#p.id', 'option' => $option_id))
									->join($this->table('_options_name') . ' as pon_' . $option_key, 'name as ' . $option_key, $where);
							} else
								$this->db->join($this->table('_product_options') . ' as po_' . $option_key, 'value as ' . $option_key, array('product' => '#p.id', 'option' => $option_id));
						}
					if ($similars = $this->db->get('array')) {
						$similars_ids = $similarProductsPhoto = array();
						foreach ($similars as $similarProduct) {
							$similars_ids[] = $similarProduct->id;
						}
						if ($photos = $this->getProductPhoto($similars_ids)) {
							$similarProductsPhoto[$photo->content] = [];
							foreach ($photos as $photo) {
								if ($sizes)
									foreach ($sizes as $resize) {
										$resize_name = $resize->prefix . '_photo';
										$similarProductsPhoto[$photo->content][$resize_name] = $_SESSION['option']->folder . '/' . $photo->content . '/' . $resize->prefix . '_' . $photo->file_name;
									}
								$similarProductsPhoto[$photo->content]['photo'] = $_SESSION['option']->folder . '/' . $photo->content . '/' . $photo->file_name;
							}
							unset($photos);
						}
						foreach ($similars as $similarProduct) {
							if (empty($similarProduct->similarFolder))
								$similarProduct->similarFolder = 'default';
							$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id] = $similarProduct;
							$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id]->photo = false;
							if (!empty($similarProductsPhoto[$similarProduct->id]))
								foreach ($similarProductsPhoto[$similarProduct->id] as $key => $value) {
									$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id]->$key = $value;
								}
							$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id]->link = $_SESSION['alias']->alias . '/' . $similarProduct->alias;
							if ($_SESSION['option']->useGroups > 0 && !empty($this->allGroups) && $_SESSION['option']->ProductMultiGroup == 0 && $similarProduct->group > 0) {
								$parents = $this->makeParents($similarProduct->group, array());
								$link = $_SESSION['alias']->alias . '/';
								foreach ($parents as $parent)
									$link .= $parent->alias . '/';
								$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id]->link = $link . $similarProduct->alias;
							} else {
								$product->similarProducts[$similarProduct->similarFolder][$similarProduct->id]->link = $_SESSION['alias']->alias . '/' . $similarProduct->alias;
							}
						}
					}
				}
			}

			// echo "<pre>";
			// print_r($product->similarProducts);
			// exit;

			if (empty($product->breadcrumbs))
				$product->breadcrumbs = $this->breadcrumbs;
			$this->db->cache_add($cache_key, $product);
			return $product;
		}
		return false;
	}

	public function updateProductsPrice(&$products)
	{
		$time = time();
		$ids = [];
		foreach ($products as $product) {
			$ids[] = $product->id;
		}
		$this->db->select($this->table('_products') . ' as p', 'id, price, old_price, currency, availability', ['id' => $ids])
			->join($this->table('_promo'), 'percent as promo_percent, from as promo_from, to as promo_to', ['id' => '#p.promo', 'status' => 1]);
		if ($_SESSION['option']->useMarkUp > 0)
			$this->db->join($this->table('_markup'), 'value as markup', array('from' => '<p.price', 'to' => '>=p.price'));
		foreach ($this->db->get('array') as $row) {
			foreach ($products as &$product) {
				if ($product->id == $row->id)
					$this->__setBaseProductPrice($product, $row, $time);
			}
		}
	}

	public function __setBaseProductPrice(&$product, $data = NULL, $time = 0)
	{

		if (!empty($data))
			foreach (['price', 'old_price', 'currency', 'markup', 'availability', 'promo', 'promo_percent', 'promo_from', 'promo_to', 'author_edit', 'date_edit'] as $key) {
				if (property_exists($data, $key))
					$product->$key = $data->$key;
			}

		if ($time == 0)
			$time = time();
		if (
			$product->promo &&
			$product->promo_percent > 0 &&
			$product->old_price < $product->price &&
			$product->promo_from < $time &&
			$product->promo_to > $time &&
			!isset($_GET['edit'])
		) {
			$product->old_price = $product->price;
			$product->price *= (100 - $product->promo_percent) / 100;
		}
	}

	public function getProductPhoto($product, $all = false)
	{
		$where['alias'] = $_SESSION['alias']->id;
		$where['content'] = $product;
		if (is_array($product) || $product == '<0') {
			$where['position'] = 1;
			$where['section_id'] = 0;
		}
		$this->db->select('wl_images', '*', $where);
		if ($all)
			$this->db->join('wl_users', 'name as user_name', '#author');
		elseif (is_numeric($product)) {
			$this->db->order('position ASC');
			$this->db->limit(1);
		}
		if (is_array($product) || $all || $product == '<0')
			return $this->db->get('array');
		else
			return $this->db->get();
	}

	private function getProductOptions($product, $parents = array())
	{
		$product_options = array();
		$where_language = $where_gon_language = '';
		if ($_SESSION['language']) {
			$where_language = "AND (po.language = '{$_SESSION['language']}' OR po.language = '')";
			$where_gon_language = "AND gon.language = '{$_SESSION['language']}'";
		}
		$this->db->executeQuery("SELECT go.id, go.alias, go.filter, go.toCart, go.main, go.photo, go.changePrice, go.sort, po.value, po.changePrice as value_changePrice, it.name as type_name, it.options, gon.name, gon.sufix 
			FROM `{$this->table('_product_options')}` as po 
			LEFT JOIN `{$this->table('_options')}` as go ON go.id = po.option 
			LEFT JOIN `{$this->table('_options_name')}` as gon ON gon.option = go.id {$where_gon_language} 
			LEFT JOIN `wl_input_types` as it ON it.id = go.type 
			WHERE go.active = 1 AND po.product = '{$product->id}' {$where_language} 
			ORDER BY go.position");
		if ($this->db->numRows() > 0) {
			$options = $this->db->getRows('array');
			foreach ($options as $option) {
				$product_options[$option->alias] = new stdClass();
				$product_options[$option->alias]->id = $option->id;
				$product_options[$option->alias]->alias = $option->alias;
				$product_options[$option->alias]->filter = $option->filter;
				$product_options[$option->alias]->toCart = $option->toCart;
				$product_options[$option->alias]->main = $option->main;
				$product_options[$option->alias]->use_options = $option->options;
				$product_options[$option->alias]->name = $option->name;
				$product_options[$option->alias]->sufix = $option->sufix;
				$product_options[$option->alias]->changePrice = $option->changePrice;
				$product_options[$option->alias]->value_changePrice = empty($option->value_changePrice) ? false : unserialize($option->value_changePrice);
				$product_options[$option->alias]->photo = false;

				if ($option->photo)
					$product_options[$option->alias]->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $option->photo;

				if ($option->options == 1) {
					if ($option->type_name == 'checkbox' || $option->type_name == 'checkbox-select2') {
						if (empty($option->value))
							continue;
						$where = array('option' => '#o.id');
						if ($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$this->db->select($this->table('_options') . ' as o', 'id, photo', array('id' => explode(',', $option->value), 'active' => 1))
							->join($this->table('_options_name') . ' as n', 'name', $where);
						if ($option->sort == 0)
							$this->db->order('position ASC');
						if ($option->sort == 1 || $option->sort == 3)
							$this->db->order('name ASC', 'n');
						if ($option->sort == 2 || $option->sort == 4)
							$this->db->order('name DESC', 'n');
						if ($list = $this->db->get('array')) {
							if ($option->sort == 3 || $option->sort == 4) {
								$for_sort = [];
								foreach ($list as $el) {
									$for_sort[] = $this->tofloat($el->name);
								}
								$sort_type = $option->sort == 3 ? SORT_ASC : SORT_DESC;
								array_multisort($for_sort, $sort_type, SORT_NUMERIC, $list);
								unset($for_sort);
							}
							foreach ($list as $el) {
								$product_options[$option->alias]->value[] = $el;
								if ($el->photo)
									$el->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $el->photo;
								if (!empty($product_options[$option->alias]->value_changePrice) && isset($product_options[$option->alias]->value_changePrice[$el->id])) {
									$el->changePrice = $product_options[$option->alias]->value_changePrice[$el->id];
								}
							}
						}
					} elseif ($option->toCart) {
						$where = array('option' => '#o.id');
						if ($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$list = $this->db->select($this->table('_options') . ' as o', 'id, photo', ['group' => -$option->id, 'active' => 1])
							->join($this->table('_options_name') . ' as n', 'name', $where);
						if ($option->sort == 0)
							$this->db->order('position ASC');
						if ($option->sort == 1 || $option->sort == 3)
							$this->db->order('name ASC', 'n');
						if ($option->sort == 2 || $option->sort == 4)
							$this->db->order('name DESC', 'n');
						if ($list = $this->db->get('array')) {
							if ($option->sort == 3 || $option->sort == 4) {
								$for_sort = [];
								foreach ($list as $el) {
									$for_sort[] = $this->tofloat($el->name);
								}
								$sort_type = $option->sort == 3 ? SORT_ASC : SORT_DESC;
								array_multisort($for_sort, $sort_type, SORT_NUMERIC, $list);
								unset($for_sort);
							}
							foreach ($list as $el) {
								$product_options[$option->alias]->value[] = $el;
								if ($el->photo)
									$el->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $el->photo;
							}
						} else
							unset($product_options[$option->alias]);
					} else {
						$where = array('option' => '#o.id');
						if ($_SESSION['language']) $where['language'] = $_SESSION['language'];
						$value = $this->db->select($this->table('_options') . ' as o', 'id, photo', ['id' => $option->value, 'active' => 1])
							->join($this->table('_options_name'), 'name', $where)
							->get('single');
						if ($value) {
							$product_options[$option->alias]->value = $value;
							if ($value->photo)
								$product_options[$option->alias]->value->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $value->photo;
						}
					}
				} else
					$product_options[$option->alias]->value = $option->value;
			}
		}
		if (!in_array(0, $parents))
			$parents[] = 0;
		$where = array('toCart' => 1, 'active' => 1, 'type' => array(9, 10));
		$where['group'] = $parents;
		$where_name = array('option' => '#o.id');
		if ($_SESSION['language']) $where_name['language'] = $_SESSION['language'];
		$this->db->select($this->table('_options') . ' as o', '*', $where)
			->join($this->table('_options_name'), 'name, sufix', $where_name);
		if ($options = $this->db->get('array')) {
			foreach ($options as $option) {
				if (!isset($product_options[$option->alias]) || empty($product_options[$option->alias]->value)) {
					$product_options[$option->alias] = new stdClass();
					$product_options[$option->alias]->id = $option->id;
					$product_options[$option->alias]->alias = $option->alias;
					$product_options[$option->alias]->filter = $option->filter;
					$product_options[$option->alias]->main = $option->main;
					$product_options[$option->alias]->toCart = $option->toCart;
					$product_options[$option->alias]->name = $option->name;
					$product_options[$option->alias]->sufix = $option->sufix;
					$product_options[$option->alias]->changePrice = $option->changePrice;
					$product_options[$option->alias]->photo = false;

					if ($option->photo)
						$product_options[$option->alias]->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $option->photo;

					if ($option->toCart) {
						$list = $this->db->select($this->table('_options') . ' as o', 'id, photo', ['group' => -$option->id, 'active' => 1])
							->join($this->table('_options_name') . ' as n', 'name, sufix', $where_name);
						if ($option->sort == 0)
							$this->db->order('position ASC');
						if ($option->sort == 1 || $option->sort == 3)
							$this->db->order('name ASC', 'n');
						if ($option->sort == 2 || $option->sort == 4)
							$this->db->order('name DESC', 'n');
						if ($list = $this->db->get('array')) {
							if ($option->sort == 3 || $option->sort == 4) {
								$for_sort = [];
								foreach ($list as $el) {
									$for_sort[] = $this->tofloat($el->name);
								}
								$sort_type = $option->sort == 3 ? SORT_ASC : SORT_DESC;
								array_multisort($for_sort, $sort_type, SORT_NUMERIC, $list);
								unset($for_sort);
							}
							foreach ($list as $el) {
								$product_options[$option->alias]->value[] = $el;
								if ($el->photo)
									$el->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $el->photo;
							}
						} else
							unset($product_options[$option->alias]);
					} else {
						$value = $this->db->select($this->table('_options') . ' as o', 'id, photo', $option->value)
							->join($this->table('_options_name'), 'name, sufix', $where_name)
							->get('single');
						if ($value) {
							$product_options[$option->alias]->value = $value;
							if ($value->photo)
								$product_options[$option->alias]->value->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $value->photo;
						}
					}
				}
			}
		}

		return $product_options;
	}

	public function getGroups($parent = 0, $use__per_page = true)
	{
		if ($_SESSION['option']->useGroups && empty($this->allGroups))
			$this->init();
		if (empty($this->allGroups))
			return false;

		$categories = array();
		foreach ($this->allGroups as $group) {
			if ($group->active) {
				if ($parent < 0)
					$categories[] = clone $group;
				else if ($group->parent == $parent)
					$categories[] = clone $group;
			}
		}
		if (empty($categories))
			return false;
		if (isset($_SESSION['option']->paginator_per_page) && $_SESSION['option']->paginator_per_page > 0 && $parent >= 0 && $use__per_page) {
			$_SESSION['option']->paginator_total = count($categories);
			if ($_SESSION['option']->paginator_total > $_SESSION['option']->paginator_per_page) {
				$start = $end = 0;
				if (isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page'] > 1)
					$start = ($_GET['page'] - 1) * $_SESSION['option']->paginator_per_page;
				$end = $start + $_SESSION['option']->paginator_per_page;
				foreach ($categories as $i => $cat) {
					if ($i >= $start && $i < $end)
						continue;
					else
						unset($categories[$i]);
				}
			}
		}

		if (!empty($categories)) {
			$link = $_SESSION['alias']->alias . '/';
			$list = $groups_ids = $groups_photos = array();
			$sizes = $this->db->getAliasImageSizes();

			if ($parent < 0)
				$groups_ids = '<0';
			else
				foreach ($categories as $g) {
					$groups_ids[] = -$g->id;
				}
			if ($photos = $this->getProductPhoto($groups_ids)) {
				foreach ($photos as $photo) {
					$groups_photos[-$photo->content] = clone $photo;
				}
				unset($photos);
			}

			if ($parent > 0)
				$link .= $this->makeLink($parent, '');

			foreach ($categories as $Group) {
				$Group->link = $link . $Group->alias;
				if ($parent < 0 && $Group->parent > 0)
					$Group->link = $link . $this->makeLink($Group->parent, $Group->alias);
				$Group->photo = null;
				if (isset($groups_photos[$Group->id])) {
					$photo = $groups_photos[$Group->id];
					if ($sizes)
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix . '_photo';
							$Group->$resize_name = $_SESSION['option']->folder . '/-' . $Group->id . '/' . $resize->prefix . '_' . $photo->file_name;
						}
					$Group->photo = $_SESSION['option']->folder . '/-' . $Group->id . '/' . $photo->file_name;
				}

				$Group->haveChild = false;
				if (!empty($this->allGroups))
					foreach ($this->allGroups as $g) {
						if ($g->parent == $Group->id) {
							$Group->haveChild = true;
							break;
						}
					}
			}
			return $categories;
		}
		return false;
	}

	public function getProductPriceWithOptions($product, $options)
	{
		$parents = array(0);
		$where = array('toCart' => 1, 'active' => 1, 'type' => array(9, 10, 11, 12));
		if (is_object($product)) {
			if ($_SESSION['option']->useGroups > 0) {
				if ($_SESSION['option']->ProductMultiGroup == 0 && !empty($product->parents))
					foreach ($product->parents as $p) {
						$parents[] = $p->id;
					}
				elseif ($_SESSION['option']->ProductMultiGroup && !empty($product->groups))
					foreach ($product->groups as $group) {
						if (!empty($group->parents))
							foreach ($group->parents as $p) {
								if (!in_array($p->id, $parents))
									$parents[] = $p->id;
							}
					}
			}
		} else if ($product = $this->db->getAllDataById($this->table('_products'), $product)) {
			if ($_SESSION['option']->useGroups > 0) {
				if (empty($this->allGroups))
					$this->init();
				if (!empty($this->allGroups)) {
					if ($_SESSION['option']->ProductMultiGroup == 0 && $product->group > 0) {
						if ($list = $this->makeParents($product->group, array()))
							foreach ($list as $p) {
								$parents[] = $p->id;
							}
					} elseif ($_SESSION['option']->ProductMultiGroup) {
						$this->db->select($this->table('_product_group') . ' as pg', 'active', $product->id, 'product');
						$this->db->join($this->table('_groups'), 'id, parent', array('id' => '#pg.group', 'active' => 1));
						if ($groups = $this->db->get('array'))
							foreach ($groups as $group) {
								$gp = array();
								if ($gp = $this->makeParents($group->id, $gp))
									foreach ($gp as $p) {
										if (!in_array($p->id, $parents))
											$parents[] = $p->id;
									}
							}
					}
				}
			}
		}

		if (is_object($product)) {
			$where['group'] = $parents;
			if ($all_options = $this->db->getAllDataByFieldInArray($this->table('_options'), $where, 'position ASC')) {
				$price = $product->price;
				$list_id = $diff = $options_values = array();
				foreach ($all_options as $option) {
					$option->value = 0;
					if (array_key_exists($option->id, $options) && is_numeric($options[$option->id])) {
						$list_id[] = $option->id;
						$option->value = $options[$option->id];
					}
				}
				if (empty($list_id))
					return $product;
				if ($settings = $this->db->getAllDataByFieldInArray($this->table('_product_options'), array('product' => $product->id, 'option' => $list_id)))
					foreach ($settings as $setting) {
						if (!empty($setting->changePrice)) {
							$list = unserialize($setting->changePrice);
							if (isset($list[$options[$setting->option]])) {
								$options_values[$setting->option] = $list[$options[$setting->option]];
								if (!empty($list[$options[$setting->option]]))
									$diff[] = $setting->option;
							} else
								$diff[] = $setting->option;
						}
					}
				if (!empty($diff))
					$list_id = array_diff($list_id, $diff);
				if (!empty($list_id)) {
					$ids = array();
					foreach ($all_options as $option) {
						if (in_array($option->id, $list_id))
							$ids[] = $option->value;
					}
					if ($default = $this->db->getAllDataByFieldInArray($this->table('_options'), array('id' => $ids)))
						foreach ($default as $option) {
							if (!empty($option->changePrice))
								$options_values[-$option->group] = unserialize($option->changePrice);
						}
				}

				foreach ($all_options as $option) {
					if ($option->value && !empty($options_values[$option->id])) {
						$changePrice = $options_values[$option->id];
						if (is_array($changePrice) && $changePrice['value'] > 0) {
							$plus = 0;
							if ($changePrice['currency'] == 'p')
								$plus = $price * $changePrice['value'] / 100;
							elseif (
								!empty($_SESSION['currency'])
								&& $changePrice['currency'] != $product->currency
								&& $changePrice['currency'] != '0'
								&& !empty($_SESSION['currency'][$changePrice['currency']])
								&& !empty($_SESSION['currency'][$product->currency])
							)
								$plus = $changePrice['value'] * $_SESSION['currency'][$changePrice['currency']] / $_SESSION['currency'][$product->currency];
							else
								$plus = $changePrice['value'];
							if ($changePrice['action'] == '=')
								$price = $plus;
							else if ($changePrice['action'] == '+')
								$price += $plus;
							else if ($changePrice['action'] == '-')
								$price -= $plus;
							else if ($changePrice['action'] == '*')
								$price *= $plus;
						}
					}
				}
				$product->price = $price;
			}
			return $product;
		}
		return false;
	}

	public function makeParents($parent, $parents)
	{
		if (isset($this->allGroups[$parent])) {
			$group = clone $this->allGroups[$parent];
			array_unshift($parents, $group);
			if ($this->allGroups[$parent]->parent > 0)
				$parents = $this->makeParents($this->allGroups[$parent]->parent, $parents);
		}
		return $parents;
	}

	public $getGroupPhoto = true;
	public function getGroupByAlias($alias, $parent = 0, $key = 'alias')
	{
		if (empty($this->allGroups))
			$this->init();
		if (empty($this->allGroups))
			return false;

		$group = false;
		if ($key == 'id') {
			if (isset($this->allGroups[$alias]))
				$group = $this->allGroups[$alias];
			else
				return false;
		} elseif ($key == 'alias') {
			foreach ($this->allGroups as $g) {
				if ($g->alias == $alias && $g->parent == $parent) {
					$group = $g;
					break;
				}
			}
		} else {
			$where['wl_alias'] = $_SESSION['alias']->id;
			$where['alias'] = $alias;
			$where['parent'] = $parent;
			$this->db->select($this->table('_groups') . ' as c', '*', $where);
			$this->db->join('wl_users', 'name as user_name', '#c.author_edit');
			$group = $this->db->get('single');
		}

		if ($group) {
			$group->haveChild = false;
			if (!empty($this->allGroups))
				foreach ($this->allGroups as $g) {
					if ($g->parent == $group->id) {
						$group->haveChild = true;
						break;
					}
				}

			$group->link = $_SESSION['alias']->alias;
			$group->parents = array();
			if ($group->parent > 0) {
				$group->parents = $this->makeParents($group->parent, $group->parents);
				foreach ($group->parents as $parent) {
					$group->link .= '/' . $parent->alias;
				}
			}
			$group->link .= '/' . $group->alias;

			if ($this->getBreadcrumbs) {
				if ($group->parent > 0 && $group->parents) {
					$link = $_SESSION['alias']->alias;
					foreach ($group->parents as $parent) {
						$link .= '/' . $parent->alias;
						$this->breadcrumbs[$parent->name] = $link;
					}
				}
				$this->breadcrumbs[$group->name] = '';
			} elseif ($this->getGroupPhoto) {
				$group->photo = false;
				if ($photo = $this->getProductPhoto(-$group->id)) {
					if ($sizes = $this->db->getAliasImageSizes())
						foreach ($sizes as $resize) {
							$resize_name = $resize->prefix . '_photo';
							$group->$resize_name = $_SESSION['option']->folder . '/-' . $group->id . '/' . $resize->prefix . '_' . $photo->file_name;
						}
					$group->photo = $_SESSION['option']->folder . '/-' . $group->id . '/' . $photo->file_name;
				}
			}
		}
		return $group;
	}

	public function initAllOptions()
	{
		$allOptions = $this->db->cache_get('allOptions');
		if ($allOptions === NULL) {
			$where = array();
			$where['wl_alias'] = $_SESSION['alias']->id;
			$where['active'] = 1;

			$this->db->select($this->table('_options') . ' as o', '*', $where);
			$this->db->join('wl_input_types', 'name as type_name', '#o.type');
			$where = array('option' => '#o.id');
			if ($_SESSION['language'])
				$where['language'] = $_SESSION['language'];
			$this->db->join($this->table('_options_name'), 'name, sufix', $where);
			$this->db->order('position ASC');

			if ($list = $this->db->get('array')) {
				foreach ($list as $g) {
					$this->allOptions[$g->id] = clone $g;
				}
				unset($list);
			}
			$this->db->cache_add('allOptions', $this->allOptions);
		} else
			$this->allOptions = $allOptions;
	}

	public function getOptionsToGroup($group = 0, $filter = true)
	{
		$products = [];
		if (empty($this->allGroups))
			$this->init();
		if ($group === 0) {
			$where['group'] = 0;
			$group = new stdClass();
			$group->id = 0;
			$group->parent = 0;
		} elseif (is_numeric($group)) {
			if (isset($this->allGroups[$group]))
				$group = $this->allGroups[$group];
			else
				return false;
		}

		$cache_key = false;
		if ($group->id > 0 && empty($_GET['name']) && empty($_GET['article'])) {
			$filterKey = $filter ? '+filter' : '-filter';
			$cache_key = 'optionsToGroup/' . $this->db->getCacheContentKey('group-', $group->id) . $filterKey;
			$cache_options = $this->db->cache_get($cache_key);
			if ($cache_options !== NULL)
				return $cache_options;
		}

		if ($filter) {
			if ($_SESSION['option']->useGroups && $group->id > 0) {
				if (empty($this->productsIdInGroup)) {
					$endGroups = $this->getEndGroups($group->id);
					if ($_SESSION['option']->ProductMultiGroup) {
						$products_id = $this->db->select($this->table('_product_group') . ' as pg', 'product', array('group' => $endGroups, 'active' => 1))
							->group('product')
							->get('array');
						if ($products_id)
							foreach ($products_id as $product) {
								$products[] = $product->product;
							}
					} else {
						$products_id = $this->db->getAllDataByFieldInArray($this->table('_products'), array('group' => $endGroups, 'active' => 1));
						if ($products_id)
							foreach ($products_id as $product) {
								$products[] = $product->id;
							}
					}
					$this->productsIdInGroup = $products;
				} else
					$products = $this->productsIdInGroup;
			} elseif (!empty($this->productsIdInGroup))
				$products = $this->productsIdInGroup;
			elseif ($group->id > 0)
				return false;
		}

		if ($filter && ($group->id > 0 && !empty($products) || $group->id == 0) || !$filter) {
			$options = $where = [];
			if (empty($this->allOptions))
				$this->initAllOptions();

			$where['group'] = array(0, $group->id);
			if ($group->parent > 0)
				while ($group->parent > 0) {
					if (isset($this->allGroups[$group->parent]))
						$group = $this->allGroups[$group->parent];
					array_push($where['group'], $group->id);
				}

			if (empty($this->allOptions)) {
				$where['wl_alias'] = $_SESSION['alias']->id;
				if ($filter)
					$where['filter'] = 1;
				$where['active'] = 1;
				$this->db->select($this->table('_options') . ' as o', '*', $where);
				$this->db->join('wl_input_types', 'name as type_name', '#o.type');
				$where = array('option' => '#o.id');
				if ($_SESSION['language']) $where['language'] = $_SESSION['language'];
				$this->db->join($this->table('_options_name'), 'name, sufix', $where);
				$this->db->order('position');
				$options = $this->db->get('array');
			} else {
				foreach ($this->allOptions as $option) {
					if ($option->filter || !$filter)
						if (in_array($option->group, $where['group']))
							$options[] = clone $option;
				}
			}

			if (!empty($options)) {
				$to_delete_options = $opt_ids = array();
				if ($filter && $products) {
					foreach ($options as $option)
						$opt_ids[] = $option->id;
					$where = array('option' => $opt_ids);
					$where['product'] = $products;
					$list_product_options = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $where);
					if (!$list_product_options) {
						if ($cache_key)
							$this->db->cache_add($cache_key, false);
						return false;
					}
				}

				$group_option_ids = $option_values = [];
				if (empty($this->allOptions)) {
					$group_option_ids['sort-0'] = [];
					$group_option_ids['sort-1-3'] = [];
					$group_option_ids['sort-2-4'] = [];
					foreach ($options as $i => $option) {
						$option->continue = false;
						if (!empty($list_product_options)) {
							$next = true;
							foreach ($list_product_options as $row) {
								if ($row->option == $option->id) {
									$next = false;
									break;
								}
							}
							if ($next) {
								if ($filter)
									$to_delete_options[] = $i;
								$option->continue = true;
								continue;
							}
						}
						if ($option->sort == 0)
							$group_option_ids['sort-0'][] = -$option->id;
						if ($option->sort == 1 || $option->sort == 3)
							$group_option_ids['sort-1-3'][] = -$option->id;
						if ($option->sort == 2 || $option->sort == 4)
							$group_option_ids['sort-2-4'][] = -$option->id;
					}

					$where = array('option' => '#o.id');
					if ($_SESSION['language'])
						$where['language'] = $_SESSION['language'];
					foreach ($group_option_ids as $sort_key => $ids) {
						if (empty($ids))
							continue;
						$this->db->select($this->table('_options') . ' as o', 'id, group, photo', ['group' => $ids, 'active' => 1]);
						$this->db->join($this->table('_options_name') . ' as n', 'name', $where);
						if ($sort_key == 'sort-0')
							$this->db->order('position ASC');
						if ($sort_key == 'sort-1-3')
							$this->db->order('name ASC', 'n');
						if ($sort_key == 'sort-2-4')
							$this->db->order('name DESC', 'n');
						if ($values = $this->db->get('array'))
							foreach ($values as $value) {
								if (empty($option_values[$value->group]))
									$option_values[$value->group] = [$value];
								else
									$option_values[$value->group][] = $value;
							}
					}
				} else {
					$group_ids = [];
					foreach ($options as $i => $option) {
						$option->continue = false;
						if (!empty($list_product_options)) {
							$next = true;
							foreach ($list_product_options as $row) {
								if ($row->option == $option->id) {
									$next = false;
									break;
								}
							}
							if ($next) {
								if ($filter)
									$to_delete_options[] = $i;
								$option->continue = true;
								continue;
							}
						}
						$group_ids[] = -$option->id;
					}
					if (!empty($group_ids))
						foreach ($group_ids as $group_id) {
							foreach ($this->allOptions as $value) {
								if ($value->group == $group_id) {
									if (!isset($option_values[$value->group]))
										$option_values[$value->group] = [];
									$option_values[$value->group][] = clone $value;
								}
							}
						}
				}

				foreach ($options as $opt_i => $option) {
					if ($option->continue)
						continue;
					$option->values = $option_values[-$option->id] ?? false;

					if (!empty($option->values)) {
						if (!empty($this->allOptions)) {
							if ($option->sort == 1 || $option->sort == 2) {
								$for_sort = [];
								foreach ($option->values as $el) {
									$for_sort[] = $el->name;
								}
								$sort_type = $option->sort == 1 ? SORT_ASC : SORT_DESC;
								array_multisort($for_sort, $sort_type, $option->values);
								unset($for_sort);
							}
						}
						if ($option->sort == 3 || $option->sort == 4) {
							$for_sort = [];
							foreach ($option->values as $el) {
								$for_sort[] = $this->tofloat($el->name);
							}
							$sort_type = $option->sort == 3 ? SORT_ASC : SORT_DESC;
							array_multisort($for_sort, $sort_type, SORT_NUMERIC, $option->values);
							unset($for_sort);
						}

						if ($filter && !empty($list_product_options)) {
							$to_delete_values = [];
							foreach ($option->values as $i => $value) {
								if ($value->photo)
									$value->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $value->photo;
								$count = 0;
								if ($option->type_name == 'checkbox' || $option->type_name == 'checkbox-select2') {
									foreach ($list_product_options as $row) {
										if ($row->option == $option->id) {
											if (!is_array($row->value))
												$row->value = explode(',', $row->value);
											if (in_array($value->id, $row->value))
												$count++;
										}
									}
								} else {
									foreach ($list_product_options as $row) {
										if ($row->option == $option->id && $row->value == $value->id)
											$count++;
									}
								}
								$option->values[$i]->count = $count;
								if (!$count && $filter)
									$to_delete_values[] = $i;
							}

							if (!empty($to_delete_values)) {
								rsort($to_delete_values);
								foreach ($to_delete_values as $i) {
									unset($option->values[$i]);
								}
							}

							// if ($filter && count($option->values) < 2) {
							// 	$to_delete_options[] = $opt_i;
							// }
						} else if ($filter) {
							$where = array();
							if ($products)
								$where['product'] = $products;
							$to_delete_values = [];
							foreach ($option->values as $i => $value) {
								if ($value->photo)
									$value->photo = IMG_PATH . $_SESSION['option']->folder . '/options/' . $option->alias . '/' . $value->photo;
								$where['option'] = $option->id;
								if ($option->type_name == 'checkbox' || $option->type_name == 'checkbox-select2') {
									$count = 0;
									$where['value'] = '%' . $value->id;
									$list = $this->db->getAllDataByFieldInArray($this->table('_product_options'), $where);
									if ($list)
										foreach ($list as $key) {
											$key->value = explode(',', $key->value);
											if (in_array($value->id, $key->value)) $count++;
										}
								} else {
									$where['value'] = $value->id;
									$count = $this->db->getCount($this->table('_product_options'), $where);
								}
								$option->values[$i]->count = $count;
								if (!$count && $filter)
									$to_delete_values[] = $i;
							}
							if (!empty($to_delete_values)) {
								rsort($to_delete_values);
								foreach ($to_delete_values as $i) {
									unset($option->values[$i]);
								}
							}
						}
					} elseif ($filter)
						$to_delete_options[] = $opt_i;
				}

				if (!empty($to_delete_options)) {
					rsort($to_delete_options);
					foreach ($to_delete_options as $i) {
						unset($options[$i]);
					}
				}
			}

			if ($cache_key)
				$this->db->cache_add($cache_key, $options);
			return $options;
		}

		if ($cache_key)
			$this->db->cache_add($cache_key, false);

		return false;
	}

	// найкраще викликати після getProducts() або getOptionsToGroup()
	public function getMinMaxPrices($group_id = 0)
	{
		if (empty($group_id))
			return false;

		$in = [];
		$res = new stdClass();
		if (empty($this->productsIdInGroup)) {
			$endGroups = $this->getEndGroups($group_id);
			if ($_SESSION['option']->ProductMultiGroup) {
				if ($products_id = $this->db->select($this->table('_product_group') . ' as pg', 'product', array('group' => $endGroups, 'active' => 1))
					->group('product')
					->get('array')
				)
					foreach ($products_id as $product) {
						$in[] = $product->product;
					}
			} else {
				$endGroups = implode(',', $endGroups);
				$res = $this->db->getQuery("SELECT MIN(`price`) as min, MAX(`price`) as max FROM `{$this->table('_products')}` WHERE `group` IN ({$endGroups}) AND `active` = 1");
				return $res;
			}
			$this->productsIdInGroup = $in;
		}

		if (empty($this->productsIdInGroup)) {
			$res->min = $res->max = 0;
			return $res;
		}

		$in = implode(',', $this->productsIdInGroup);
		$res = $this->db->getQuery("SELECT MIN(`price`) as min, MAX(`price`) as max FROM `{$this->table('_products')}` WHERE `id` IN ({$in})");
		return $res;
	}

	private function makeLink($parent, $link)
	{
		$link = $this->allGroups[$parent]->alias . '/' . $link;
		if ($this->allGroups[$parent]->parent > 0)
			$link = $this->makeLink($this->allGroups[$parent]->parent, $link);
		return $link;
	}

	public function getEndGroups($parentGroups)
	{
		$endGroups = $groups = array();
		if (empty($this->allGroups))
			$this->init();
		if (empty($this->allGroups))
			return false;
		foreach ($this->allGroups as $group) {
			if (isset($groups[$group->parent]))
				$groups[$group->parent][] = $group->id;
			else
				$groups[$group->parent] = array($group->id);
		}
		if (!is_array($parentGroups))
			$parentGroups = array($parentGroups);
		return $this->makeEndGroups($groups, $parentGroups, $endGroups);
	}

	private function makeEndGroups($all, $parentGroups, $endGroups)
	{
		$endGroups = array_merge($endGroups, $parentGroups);
		foreach ($parentGroups as $parent) {
			if (isset($all[$parent]))
				$endGroups = $this->makeEndGroups($all, $all[$parent], $endGroups);
		}
		return $endGroups;
	}

	// find => -1 // without resalts
	public function searchHistory($search_key, $find = -1)
	{
		$data = [];
		$data['user'] = !empty($_SESSION['user']->id) ? $_SESSION['user']->id : 0;
		$data['date'] = strtotime('today');
		$data['search_url'] = $this->data->url(true);
		$data['search_key'] = sha1($search_key . '-' . $data['date'] . '-' . $data['user'] . '-' . $data['search_url']);

		if ($search = $this->db->getAllDataById($this->table('_search_history'), $data['search_key'], 'search_key')) {
			$this->db->updateRow($this->table('_search_history'), ['find' => $find, 'count_per_day' => $search->count_per_day + 1, 'last_view' => time()], $search->id);
			return true;
		}

		$data['search_by'] = $search_key;
		$data['find'] = $find;
		$data['title'] = $_SESSION['alias']->name;
		$data['last_view'] = time();
		$data['count_per_day'] = 1;
		$this->db->insertRow($this->table('_search_history'), $data);
		return true;
	}

	public function rePositionProductsInGroup($group_id)
	{
		$table = '_products';
		if ($_SESSION['option']->ProductMultiGroup)
			$table = '_product_group';
		if ($products = $this->db->getAllDataByFieldInArray($this->table($table), $group_id, 'group', 'position ASC')) {
			$good_position = 1;
			foreach ($products as $product) {
				if ($product->position != $good_position)
					$this->db->updateRow($this->table($table), ['position' => $good_position], $product->id);
				$good_position++;
			}
		}
	}

	public function formatPrice($price, $return_price_number = false)
	{
		if (!is_array($_SESSION['option']->price_format) && !empty($_SESSION['option']->price_format))
			$_SESSION['option']->price_format = unserialize($_SESSION['option']->price_format);

		$before = $after = '';
		$penny = 1;
		$round = 2;
		if (isset($_SESSION['option']->price_format['before']))
			$before = $_SESSION['option']->price_format['before'];
		if (isset($_SESSION['option']->price_format['after']))
			$after = $_SESSION['option']->price_format['after'];
		if (isset($_SESSION['option']->price_format['round']))
			$round = $_SESSION['option']->price_format['round'];
		if (isset($_SESSION['option']->price_format['penny']))
			$penny = $_SESSION['option']->price_format['penny'];
		$price = round($price, $round);
		if ($return_price_number)
			return $price;
		// $price = round($price * 20) / 20;
		if ($penny == 0 || $penny == 2)
			$price = number_format($price, $penny, ',', '&nbsp;');
		return $before . $price . $after;
	}

	public function prepareArticleKey($text)
	{
		$text = (string) $text;
		$text = trim($text);
		$text = mb_strtolower($text, "utf-8");
		$ua = array('-', '_', ' ', '`', '~', '!', '@', '#', '$', '%', '^', '&', '"', ',', '\.', '\?', '/', ';', ':', '\'', '[+]', '“', '”');
		$en = array('', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');
		for ($i = 0; $i < count($ua); $i++) {
			$text = mb_eregi_replace($ua[$i], $en[$i], $text);
		}
		$text = mb_eregi_replace("[-]{2,}", '-', $text);
		return $text;
	}

	public function tofloat($num)
	{
		$dotPos = strrpos($num, '.');
		$commaPos = strrpos($num, ',');
		$sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos : ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

		if (!$sep) {
			return floatval(preg_replace("/[^0-9]/", "", $num));
		}

		return floatval(
			preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
				preg_replace("/[^0-9]/", "", substr($num, $sep + 1, strlen($num)))
		);
	}
}
