<?php
namespace ashupp\KendoFilters;
use Carbon\Carbon;

Class Filters
{
	public static function processFilters($filters = [], $dateFields = [])
	{
		$processedFilters = [];

		foreach ($filters as $filter) {
		    $filter['originalData'] = $filter;
			if(array_search($filter['field'], $dateFields) != false) {
				$filter['value'] = new Carbon($filter['value']);
			}
			else {
				switch (strtolower($filter['operator'])) {
					case 'startswith':
						$filter['value'] = $filter['value'] . '%';
						break;

					case 'endswith':
						$filter['value'] = '%' . $filter['value'];
						break;

					case 'contains':
					case 'doesnotcontain':
                    case '*':
						$filter['value'] = '%' . $filter['value'] . '%';
						break;

					default:
						$filter['value'] = $filter['value'];
						break;
				}

			}

			switch (strtolower($filter['operator'])) {
				case 'eq':
				case '=':
					$filter['operator'] = '=';
					break;

				case 'neq':
				case '!=':
					$filter['operator'] = '!=';
					break;

				case 'gte':
				case '>=':
					$filter['operator'] = '>=';
					break;

				case 'gt':
				case '>':
					$filter['operator'] = '>';
					break;

				case 'lte':
				case '<=':
					$filter['operator'] = '<=';
					break;

				case 'lt':
				case '<':
					$filter['operator'] = '<';
					break;

				case 'startswith':
				case 'contains':
				case 'endswith':
                case '*':
					$filter['operator'] = 'LIKE';
					break;

				case 'doesnotcontain':
					$filter['operator'] = 'NOT LIKE';
					break;
				default:
					break;
			}
			array_push($processedFilters, $filter);
		}
		return $processedFilters;
	}

	public static function addFilters($query, $filters = [], $dateFields = []) {
		$filters = Filters::processFilters($filters, $dateFields);
		foreach ($filters as $filter) {
			if(count(explode('.', $filter['field'])) > 1) {
				$fieldDetail = explode('.', $filter['field']);
				$query->whereHas($fieldDetail[0], function($query) use($fieldDetail, $filter) {
					if($filter['value'] == 'null') {
						if($filter['operator'] == '=') {
							$query->whereNull($fieldDetail[0]);
						}
						else {
							$query->whereNotNull($fieldDetail[0]);
						}
					}
					else {
						$query->where($fieldDetail[1], $filter['operator'], $filter['value']);
					}
				});
			}
			else {
				if($filter['value'] == 'null') {
					if($filter['operator'] == '=') {
						$query->whereNull($filter['field']);
					}
					else {
						$query->whereNotNull($filter['field']);
					}
				}
				else {
					$query->where($filter['field'], $filter['operator'], $filter['value']);
				}
			}
		}

		return $query;
	}
}
