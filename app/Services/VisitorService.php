<?php

namespace App\Services;

use CoreConstants;
use App\Models\Visitor;
use App\Services\Contracts\VisitorInterface;
use Carbon\Carbon;
use DB;
use Log;
use Validator;
use Jenssegers\Agent\Agent;

class VisitorService implements VisitorInterface
{
    /**
     * Eloquent instance
     *
     * @var Visitor
     */
    private $model;

    /**
     * Create a new service instance
     *
     * @param Visitor $visitor
     * @return void
     */
    public function __construct(Visitor $visitor)
    {
        $this->model = $visitor;
    }

    /**
     * Get all fields
     *
     * @param array $select
     * @return array
     */
    public function getAll(array $select = ['*'])
    {
        try {
            $result = $this->model->select($select)->get();
            
            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Store/update data
     *
     * @param array $data
     * @return array
     */
    public function store(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'tracking_id' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status' => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $agent = new Agent();

            $trackingId = $data['tracking_id'];
            $isNew = true;
            $existingData = $this->getByTrackingId($trackingId);

            if ($existingData['status'] === CoreConstants::STATUS_CODE_SUCCESS) {
                $isNew = false;
            }

            $ip = request()->getClientIp();
            $isDesktop = $agent->isDesktop();
            $browser = $agent->browser();
            $platform = $agent->platform();
            $locationData = $this->resolveLocationData($ip);

            $newData['tracking_id'] = $trackingId;
            $newData['is_new'] = $isNew;
            $newData['ip'] = $ip;
            $newData['is_desktop'] = $isDesktop;
            $newData['browser'] = $browser;
            $newData['platform'] = $platform;
            $newData['location'] = $locationData['location'];
            $newData['country_code'] = $locationData['country_code'];
            $newData['region'] = $locationData['region'];
            $newData['region_code'] = $locationData['region_code'];
            $newData['city'] = $locationData['city'];
            $newData['zip'] = $locationData['zip'];
            $newData['latitude'] = $locationData['latitude'];
            $newData['longitude'] = $locationData['longitude'];
            $newData['timezone'] = $locationData['timezone'];
            
            if (isset($data['id'])) {
                $result = $this->getById($data['id'], ['id']);
                if ($result['status'] !== CoreConstants::STATUS_CODE_SUCCESS) {
                    return $result;
                } else {
                    $existingData = $result['payload'];
                }
                $result = $existingData->update($newData);
            } else {
                $result = $this->model->create($newData);
            }

            if ($result) {
                return [
                    'message' => isset($data['id']) ? 'Data is successfully updated' : 'Data is successfully saved',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Store item for seed
     *
     * @param array $request
     * @return array
     */
    public function forceStore(array $data)
    {
        try {
            $validate = Validator::make($data, [
                'tracking_id' => 'required',
                'is_new' => 'required',
                'ip' => 'required',
                'is_desktop' => 'required',
                'browser' => 'required',
                'platform' => 'required',
                'location' => 'required',
            ]);

            if ($validate->fails()) {
                return [
                    'message' => 'Validation Error',
                    'payload' => $validate->errors(),
                    'status'  => CoreConstants::STATUS_CODE_BAD_REQUEST
                ];
            }

            $newData['tracking_id'] = $data['tracking_id'];
            $newData['is_new'] = $data['is_new'];
            $newData['ip'] = $data['ip'];
            $newData['is_desktop'] = $data['is_desktop'];
            $newData['browser'] = $data['browser'];
            $newData['platform'] = $data['platform'];
            $newData['location'] = $data['location'];
            isset($data['country_code']) && $newData['country_code'] = $data['country_code'];
            isset($data['region']) && $newData['region'] = $data['region'];
            isset($data['region_code']) && $newData['region_code'] = $data['region_code'];
            isset($data['city']) && $newData['city'] = $data['city'];
            isset($data['zip']) && $newData['zip'] = $data['zip'];
            isset($data['latitude']) && $newData['latitude'] = $data['latitude'];
            isset($data['longitude']) && $newData['longitude'] = $data['longitude'];
            isset($data['timezone']) && $newData['timezone'] = $data['timezone'];
            isset($data['created_at']) && $newData['created_at'] = $data['created_at'];

            $response = $this->model->create($newData);

            if ($response) {
                return [
                    'message' => 'Data is successfully saved',
                    'payload' => $response,
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Something went wrong',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Fetch item by tracking id
     *
     * @param string $trackingId
     * @param array $select
     * @return array
     */
    public function getByTrackingId(string $trackingId, array $select = ['*'])
    {
        try {
            $data = $this->model->select($select)->where('tracking_id', $trackingId)->first();
            
            if ($data) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $data,
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result is found',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Fetch item by id
     *
     * @param int $id
     * @param array $select
     * @return array
     */
    public function getById(int $id, array $select = ['*'])
    {
        try {
            $data = $this->model->select($select)->where('id', $id)->first();
            
            if ($data) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $data,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result is found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get all fields with paginate
     *
     * @param array $data
     * @param array $select
     * @return array
     */
    public function getAllWithPaginate(array $data, array $select = ['*'])
    {
        try {
            $perPage  = !empty($data['params']) && !empty(json_decode($data['params'])->pageSize) ? json_decode($data['params'])->pageSize : 10;
            
            if (!empty($data['sorter']) && count(json_decode($data['sorter'], true))) {
                $sorter = json_decode($data['sorter'], true);
                foreach ($sorter as $key => $value) {
                    $sortBy = $key;
                    $sortType = ($value === 'ascend' ? 'asc' : 'desc');
                }
            } else {
                $sortBy = 'created_at';
                $sortType = 'desc';
            }
            
            $result = $this->model->select($select)->orderBy($sortBy, $sortType);

            if (!empty($data['params']) && !empty(json_decode($data['params'])->keyword) && json_decode($data['params'])->keyword !== '') {
                $searchQuery = json_decode($data['params'])->keyword;
                $columns = !empty($data['columns']) ? $data['columns'] : null;
                
                if ($columns) {
                    $result->where(function ($query) use ($columns, $searchQuery) {
                        foreach ($columns as $key => $column) {
                            if (!empty(json_decode($column)->search) && json_decode($column)->search === true) {
                                $fieldName = json_decode($column)->dataIndex;
                                $query->orWhere($fieldName, 'like', '%' . $searchQuery . '%');
                            }
                        }
                    });
                }
            }

            $result = $result->paginate($perPage);
            
            if ($result) {
                return [
                    'message' => 'Data is fetched successfully',
                    'payload' => $result,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'No result found',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Delete items by id array
     *
     * @param array $ids
     * @return array
     */
    public function deleteByIds(array $ids)
    {
        try {
            $data = $this->model->whereIn('id', $ids)->delete();
            
            if ($data) {
                return [
                    'message' => 'Data is deleted successfully',
                    'payload' => $data,
                    'status' => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Nothing to Delete',
                    'payload' => null,
                    'status' => CoreConstants::STATUS_CODE_ERROR
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status' => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Get visitors stats
     *
     * @param string $startDate UTC start date
     * @param string $endEnd UTC end date
     * @return array
     */
    public function getVisitorsStats($startDate = null, $endEnd = null, $countryLimit = 5, $regionLimit = 5)
    {
        try {
            $result = $this->model;
            $countryLimit = (int) $countryLimit;
            $regionLimit = (int) $regionLimit;
            $countryLimit = $countryLimit > 0 ? $countryLimit : 5;
            $regionLimit = $regionLimit > 0 ? $regionLimit : 5;

            if ($startDate) {
                $startDate = Carbon::parse($startDate)->format('Y-m-d H:i:s');
                $result = $result->where('created_at', '>=', $startDate);
            }
            if ($endEnd) {
                $endEnd = Carbon::parse($endEnd)->format('Y-m-d H:i:s');
                $result = $result->where('created_at', '<=', $endEnd);
            }

            //visitors
            $data['visitors']['total'] = $totalVisitors = (clone $result)->count();
            $data['visitors']['new'] = (clone $result)->where('is_new', CoreConstants::TRUE)->count();
            $data['visitors']['old'] = (clone $result)->where('is_new', CoreConstants::FALSE)->count();
            
            //location
            $data['location'] = (clone $result)->select('location', DB::raw('count(*) as total'))->groupBy('location')->get();

            //device
            if ($totalVisitors) {
                $data['device']['desktop'] = ((clone $result)->where('is_desktop', CoreConstants::TRUE)->count());
                $data['device']['mobile'] = ((clone $result)->where('is_desktop', CoreConstants::FALSE)->count());

                // $data['device']['mobile'] = ((clone $result)->where('is_desktop', CoreConstants::FALSE)->count() * 100) / $totalVisitors;
            } else {
                $data['device']['desktop'] = 0;
                $data['device']['mobile'] = 0;
            }

            //country
            $data['country']['top'] = (clone $result)
                ->select('location', DB::raw('count(*) as total'))
                ->whereNotNull('location')
                ->where('location', '<>', '')
                ->groupBy('location')
                ->orderByDesc('total')
                ->limit($countryLimit)
                ->get();

            //region
            $data['region']['top'] = (clone $result)
                ->select('region', DB::raw('count(*) as total'))
                ->whereNotNull('region')
                ->where('region', '<>', '')
                ->groupBy('region')
                ->orderByDesc('total')
                ->limit($regionLimit)
                ->get();

            //device by region
            $data['region_device'] = (clone $result)
                ->select('region', 'is_desktop', DB::raw('count(*) as total'))
                ->groupBy('region', 'is_desktop')
                ->get();

            //browser
            $data['browser'] = (clone $result)->select('browser', DB::raw('count(*) as total'))->groupBy('browser')->get();

            //platform
            $data['platform'] = (clone $result)->select('platform', DB::raw('count(*) as total'))->groupBy('platform')->get();

            //ip
            $data['ip']['unique'] = (clone $result)
                ->whereNotNull('ip')
                ->where('ip', '<>', '')
                ->distinct()
                ->count('ip');
            $data['ip']['top'] = (clone $result)
                ->select('ip', DB::raw('count(*) as total'))
                ->whereNotNull('ip')
                ->where('ip', '<>', '')
                ->groupBy('ip')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            //city
            $data['city']['unique'] = (clone $result)
                ->whereNotNull('city')
                ->where('city', '<>', '')
                ->distinct()
                ->count('city');
            $data['city']['top'] = (clone $result)
                ->select('city', DB::raw('count(*) as total'))
                ->whereNotNull('city')
                ->where('city', '<>', '')
                ->groupBy('city')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            //recent visitors
            $data['recent'] = (clone $result)
                ->select([
                    'id',
                    'is_new',
                    'ip',
                    'is_desktop',
                    'browser',
                    'platform',
                    'location',
                    'country_code',
                    'region',
                    'region_code',
                    'city',
                    'zip',
                    'latitude',
                    'longitude',
                    'timezone',
                    'created_at',
                ])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();


            return [
                'message' => 'Data is fetched Successfully',
                'payload' => $data,
                'status'  => CoreConstants::STATUS_CODE_SUCCESS
            ];
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Delete all entries
     *
     * @return array
     */
    public function deleteAll()
    {
        try {
            $data = $this->model->truncate();
            
            if ($data) {
                return [
                    'message' => 'Stats are reset successfully',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_SUCCESS
                ];
            } else {
                return [
                    'message' => 'Nothing to remove',
                    'payload' => null,
                    'status'  => CoreConstants::STATUS_CODE_NOT_FOUND
                ];
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return [
                'message' => 'Something went wrong',
                'payload' => $th->getMessage(),
                'status'  => CoreConstants::STATUS_CODE_ERROR
            ];
        }
    }

    /**
     * Resolve location fields safely without external package coupling.
     *
     * @param string|null $ip
     * @return array
     */
    private function resolveLocationData(?string $ip): array
    {
        return [
            'location' => 'Unknown',
            'country_code' => null,
            'region' => null,
            'region_code' => null,
            'city' => null,
            'zip' => null,
            'latitude' => null,
            'longitude' => null,
            'timezone' => null,
        ];
    }
}
