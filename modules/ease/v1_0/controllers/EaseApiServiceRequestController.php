<?php

class EaseApiServiceRequestController extends BaseController
{
    public $data;
    public $settings;

    public function __construct()
    {

        /*$this->data->model = $this->settings->model;
        $this->data->rows = $this->settings->rows;
        $this->data->view = $this->settings->view;
        $this->data->settings = $this->settings;*/
    }
    //-------------------------------------------------------
    function postServiceRequestListNearBy() {
        $input = (Object)Input::all();
        /*
         * filter are service nationality not_started number_of_providers city profession_level */
        $apikey = $input->apikey;
        $city=$input->city;
        $user = User::where('apikey',$apikey)->first();
        if($user->group_id != 2){
            $response=[];
            $response['status']="failed";
            $response['data']="you are not a provider";
            return json_encode($response);
        }
        $easeUser = EaseUser::where('user_id',$user->id)->first();
        $nationality= $easeUser->nationality;
        if($easeUser->verified=="false"){
            $response=[];
            $response['status']="failed";
            $response['data']="you are not a verified";
            return json_encode($response);
        }
        $easeProvider = EaseProvider::where('ease_user_id',$easeUser->_id)->first();
        $provider_id = $easeProvider->_id;
        $profession_level = $easeProvider->profession_level;
        $easeProviderInstance=[];
        $easeProviderInstance['_id']=$easeProvider->_id;
        $easeProviderInstance['is_available']=true;
        //updating the provider with availability
        $res = EaseProvider::store($easeProviderInstance);
        //whereIn('id', array(1, 2, 3));
        $provider_services = [];
        $ease_provider_services = EaseProviderService::where('ease_provider_id',$provider_id)->get();
        foreach($ease_provider_services as $ease_provider_service){
            array_push($provider_services,$ease_provider_service->ease_service_id);
        }
        $country = EaseCountry::where('nationality',$nationality)->first();
        $ease_country_id = $country->id;
        $ease_service_requests_filter =  EaseServiceRequest::whereIn('ease_service_id',$provider_services)->where('scheduled',"false")->where('status','not_started')->where('city',$city)->where('ease_country_id',$ease_country_id)->where('profession_level',$profession_level)->get();
        $serviceRequests=[];
        $serviceRequests['status']="success";
        $serviceRequests['data']=$ease_service_requests_filter;

        return json_encode($serviceRequests);
    }
    //-------------------------------------------------------
    function postServiceRequestBy() {
        $input = (Object)Input::all();
        $service_id=$input->service_id;
        $serviceRequestListNearBy = EaseServiceRequest::where('ease_service_id',$service_id)->get()->toJson();
        return $serviceRequestListNearBy;
    }
    //-------------------------------------------------------
    function postSeekerServiceRequestHistory() {
        $input = (Object)Input::all();
        $seeker_id=$input->seeker_id;
        $serviceRequestHistory = EaseServiceRequest::where('ease_seeker_id',$seeker_id)->toJson();
        return $serviceRequestHistory;
    }
    //-------------------------------------------------------
    function postProviderRequestHistory() {
        $input=(Object)Input::all();
        $provider_id=$input->provider_id;
        $serviceRequestHistory = EaseProviderServiceRequest::where('ease_provider_id',$provider_id)->toJson();
        return $serviceRequestHistory;
    }
    //-------------------------------------------------------
    function postServiceRequestLogTime() {
        $input=(Object)Input::all();
        $request_id=$input->ease_service_request_id;
        $serviceRequestLogTime = EaseServiceTimelog::where('ease_service_request_id',$request_id)->toJson();
        return $serviceRequestLogTime;
    }
    //-------------------------------------------------------
    function postCurrentProviderCount() {
        $input=(Object)Input::all();
        $service_request_id=$input->ease_service_request_id;
        $providers = EaseProviderServiceRequest::where('ease_service_request_id',$service_request_id)->get();
        $numberOfProvider = count($providers);
        return $numberOfProvider;
    }
    //-------------------------------------------------------
    //-------------------------------------------------------
    function postStartService() {
        $input=(Object)Input::all();
        $ease_service_request_id=$input->ease_service_request_id;
        $easeServiceTimeLog=[];
        $easeServiceTimeLog['ease_service_request_id']=$ease_service_request_id;
        $timestamp= Dates::now();
        $easeServiceTimeLog['start_time']=$timestamp;
        $easeServiceTimeLog['status']='going_on';

        $this->beforeFilter(function () {
            if (!Permission::check($this->data->prefix . '-create')) {
                $error_message = "You don't have permission create";
                if (isset($this->data->input->format) && $this->data->input->format == "json") {
                    $response['status'] = 'failed';
                    $response['errors'][] = $error_message;
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::route('error')->with('flash_error', $error_message);
                }
            }
        });
        $response = EaseServiceTimeLog::store($easeServiceTimeLog);
        echo json_encode($response);
        die();
        return $responce;
    }
    //-------------------------------------------------------
    function postStopService() {
        $input=(Object)Input::all();
        $ease_service_request_id=$input->ease_service_request_id;
        $easeServiceTimeLog=[];
        $easeServiceTimeLog['ease_service_request_id']=$ease_service_request_id;
        $timestamp= Dates::now();
        $easeServiceTimeLog['stop_time']=$timestamp;
        $easeServiceTimeLog['status']='complete';

        $this->beforeFilter(function () {
            if (!Permission::check($this->data->prefix . '-create')) {
                $error_message = "You don't have permission create";
                if (isset($this->data->input->format) && $this->data->input->format == "json") {
                    $response['status'] = 'failed';
                    $response['errors'][] = $error_message;
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::route('error')->with('flash_error', $error_message);
                }
            }
        });
        $response = EaseServiceTimeLog::store($easeServiceTimeLog);
        echo json_encode($response);
        die();
        return $responce;
    }
    //-------------------------------------------------------
    function index()
    {
        $model = $this->data->model;
        if (isset($this->data->input->show) && $this->data->input->show == 'trash') {
            $this->data->list = $model::getTrash();
        } else {
            if (isset($this->data->input->q) && !empty($this->data->input->q)) {
                $this->data->list = $this->search();
            } else {
                $list = $model::orderBy("created_at", "ASC");
                $this->data->list = $list->paginate($this->data->rows);
            }
        }
        $this->data->trash_count = $model::getTrashCount();
        return View::make($this->data->view . 'index')->with('title', 'Item List')->with('data', $this->data);
    }

    //------------------------------------------------------
    function search()
    {
        $model = $this->data->model;
        $list = $model::orderBy("created_at", "ASC");
        $term = $this->data->input->q;
        $list->where('name', 'LIKE', '%' . $term . '%');
        $list->orWhere('slug', 'LIKE', '%' . $term . '%');
        $list->orWhere('id', '=', $term);
        $result = $list->paginate($this->data->rows);
        return $result;
    }

    //------------------------------------------------------
    function create()
    {
        $this->beforeFilter(function () {
            if (!Permission::check($this->data->prefix . '-create')) {
                $error_message = "You don't have permission create";
                if (isset($this->data->input->format) && $this->data->input->format == "json") {
                    $response['status'] = 'failed';
                    $response['errors'][] = $error_message;
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::route('error')->with('flash_error', $error_message);
                }
            }
        });
        $model = $this->data->model;
        $response = $model::store();
        echo json_encode($response);
        die();
    }

    //------------------------------------------------------
    function read($id)
    {
        $this->beforeFilter(function () {
            if (!Permission::check($this->data->prefix . '-read')) {
                $error_message = "You don't have permission read";
                if (isset($this->data->input->format) && $this->data->input->format == "json") {
                    $response['status'] = 'failed';
                    $response['errors'][] = $error_message;
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::route('error')->with('flash_error', $error_message);
                }
            }
        });
        $model = $this->data->model;
        $item = $model::withTrashed()->where('id', $id)->first();
        if ($item) {
            $item->createdBy;
            $item->modifiedBy;
            $item->deletedBy;
            $response['status'] = 'success';
            $response['data'] = $item;
        } else {
            $response['status'] = 'success';
            $response['errors'][] = 'Not found';
        }
        if ($this->data->input->format == 'json') {
            $response_in_json = json_encode($item);
            $response['html'] = View::make($this->data->view . 'elements.view-item')
                ->with('data', json_decode($response_in_json))
                ->render();
            echo json_encode($response);
            die();
        } else {
            return $response;
        }
    }

    //------------------------------------------------------
    function update()
    {
        $this->beforeFilter(function () {
            if (!Permission::check($this->data->prefix . '-update')) {
                $error_message = "You don't have permission update";
                if (isset($this->data->input->format) && $this->data->input->format == "json") {
                    $response['status'] = 'failed';
                    $response['errors'][] = $error_message;
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::route('error')->with('flash_error', $error_message);
                }
            }
        });
        $model = $this->data->model;
        $response = $model::store();
        echo json_encode($response);
        die();
    }
    //------------------------------------------------------
    //------------------------------------------------------
    function enable()
    {
        $model = $this->data->model;
        if (isset($this->data->input->pk)) {
            $input['id'] = $this->data->input->pk;
            $input['enable'] = 1;
            $response = $model::store($input);
            echo json_encode($response);
            die();
        } else if (is_array($this->data->input->id)) {
            if (empty($this->data->input->id)) {
                $response['status'] = 'failed';
                $response['errors'][] = constant('core_no_item_selected');
                echo json_encode($response);
                die();
            }
            foreach ($this->data->input->id as $id) {
                $input['id'] = $id;
                $input['enable'] = 1;
                $model::store($input);
            }
        }
    }

    //------------------------------------------------------
    function disable()
    {
        $model = $this->data->model;
        if (isset($this->data->input->pk)) {
            $input['id'] = $this->data->input->pk;
            $input['enable'] = 0;
            $response = $model::store($input);
            echo json_encode($response);
            die();
        } else if (is_array($this->data->input->id)) {
            if (empty($this->data->input->id)) {
                $response['status'] = 'failed';
                $response['errors'][] = constant('core_no_item_selected');
                echo json_encode($response);
                die();
            }
            foreach ($this->data->input->id as $id) {
                $input['id'] = $id;
                $input['enable'] = 0;
                $model::store($input);
            }
        }
    }

    //------------------------------------------------------
    function delete()
    {
        $model = $this->data->model;
        if (isset($this->data->input->pk)) {
            $response = $model::deleteItem($this->data->input->pk);
            echo json_encode($response);
            die();
        } else if (is_array($this->data->input->id)) {
            if (empty($this->data->input->id)) {
                $response['status'] = 'failed';
                $response['errors'][] = constant('core_no_item_selected');
                echo json_encode($response);
                die();
            }
            foreach ($this->data->input->id as $id) {
                $model::deleteItem($id);
            }
        }
    }

    //------------------------------------------------------
    function bulkAction()
    {
        $model = $this->data->model;
        if ($this->data->input->action == 'search') {
            $input = Input::all();
            unset($input['_token']);
            return Redirect::route($this->data->prefix . "-index", $input);
        }
        if (!Permission::check($this->data->prefix . '-update')) {
            $response['status'] = 'failed';
            $response['errors'][] = constant('core_msg_permission_denied');
        }
        if (!isset($this->data->input->pk) && !isset($this->data->input->id)) {
            $response['status'] = 'failed';
            $response['errors'][] = constant('core_no_item_selected');
        }
        if (isset($response['status'])
            && $response['status'] == 'failed'
            && isset($this->data->input->format)
            && $this->data->input->format == 'json'
        ) {
            echo json_encode($response);
            die();
        } else if (isset($response['status']) && $response['status'] == 'failed') {
            return Redirect::back()->with('flash_error', $response['errors'][0]);
        }
        switch ($this->data->input->action) {
            case 'enable':
                $this->enable();
                if (isset($this->data->input->format) && $this->data->input->format == 'json') {
                    $response['status'] = 'success';
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::back()->with('flash_success', constant('core_success'));
                }
                break;
            //------------------------------
            case 'disable':
                $this->disable();
                if (isset($this->data->input->format) && $this->data->input->format == 'json') {
                    $response['status'] = 'success';
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::back()->with('flash_success', constant('core_success'));
                }
                break;
            //------------------------------
            case 'delete':
                $this->delete();
                if (isset($this->data->input->format) && $this->data->input->format == 'json') {
                    $response['status'] = 'success';
                    echo json_encode($response);
                    die();
                } else {
                    return Redirect::back()->with('flash_success', constant('core_success'));
                }
                break;
            //------------------------------
            case 'restore':
                foreach ($this->data->input->id as $id) {
                    $model::withTrashed()->where('id', $id)->restore();
                }
                return Redirect::back()->with('flash_success', constant('core_msg_permission_denied'));
                break;
            //------------------------------
            case 'permanent_delete':
                foreach ($this->data->input->id as $id) {
                    $model::withTrashed()->where('id', $id)->forceDelete();
                }
                return Redirect::back()->with('flash_success', constant('core_success_message'));
                break;
            //------------------------------
        }
    }
    //------------------------------------------------------
    //------------------------------------------------------
} // end of the class