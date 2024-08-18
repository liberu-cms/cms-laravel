<?php

namespace App\Services;

use App\Exceptions\ExceptionHandler;
use Illuminate\Database\Eloquent\Model;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class EntityManagementService extends Controller
{
    public function show($id, $model, $with = []){
        try {
            $data = $model::with($with)->find($id);
            if ($data == null) {
                return $this->responseMessage("{(new $model)->activity_module} not found", 406);
            }
            return Response::json($data);
        } catch (\Exception $error) {
            throw new ExceptionHandler($error);
        }
    }

    /**
     * @param $model, $data, $key
     * @param Model $model
     * @param array $data fillable data
     * @param string $key column name for render data
     * @return \Illuminate\Http\Response
     */
    public function store($model, array $data)
    {
        try {
            DB::beginTransaction();
            $modelData = $model::create($data);
            DB::commit();
            return $this->httpResponse($modelData, null, $data[(new $model)->activity_column]);
        } catch (\Exception $error) {
            DB::rollBack();
            throw new ExceptionHandler($error);
        }
    }

    /**
     * @param $model, $id, $data, $module, $key
     * @param Model $model
     * @param array $id primary key
     * @param array $data fillable data
     * @param string $module module name
     * @param string $key column name for render data
     * @return \Illuminate\Http\Response
     */
    public function update($model, int $id, array $data)
    {
        try {
            DB::beginTransaction();
            $modelData = $model::find($id);
            if ($modelData == null) {
                return $this->responseMessage("{(new $model)->activity_module} not found", 406);
            }
            $modelData->update($data);
            DB::commit();
            return $this->httpResponse($modelData, null, $data[(new $model)->activity_column]);
        } catch (\Exception $error) {
            DB::rollBack();
            throw new ExceptionHandler($error);
        }
    }

    /**
     * @param $model, $id, $module, $key
     * @param Model $model
     * @param array $id primary key
     * @param string $module module name
     * @param string $key column name for render data
     * @return \Illuminate\Http\Response
     */
    public function destroy($model, int $id)
    {
        try {
            DB::beginTransaction();
            $modelData = $model::find($id);
            if ($modelData == null) {
                return $this->responseMessage("{(new $model)->activity_module} not found", 406);
            }
            $modelData->delete();
            DB::commit();
            return $this->httpResponse($modelData, "{$modelData[(new $model)->activity_column]} Deleted Successfully");
        } catch (\Exception $error) {
            DB::rollBack();
            throw new ExceptionHandler($error);
        }
    }
}
