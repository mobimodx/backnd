<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use GuzzleHttp\Client;
use BenBjurstrom\Replicate\Replicate;

class ApiController extends Controller
{
    public function GetApis(Request $request)
    {
        $tokens = '$2y$10$beZC9ycZaKOyd';
        $data = [
            'text_to_img' => env('APP_URL').'/api/text_to_img?token='.$tokens,
            'img_to_img' => env('APP_URL').'/api/img_to_img?token='.$tokens,
            'outfit' => env('APP_URL').'/api/outfit?token='.$tokens,
            'interior_design' => env('APP_URL').'/api/interior_design?token='.$tokens
        ];

        return json_encode(['status' => true, 'data' => $data]);
    }
    public function TextToImg(Request $request)
    {
        $tokens = '$2y$10$beZC9ycZaKOyd';
        if($tokens!=$request->token){
            return json_encode(['status' => false, 'message' => 'Unauthorized access!']);
        }
        $client = new Client();
        $curl = curl_init();

        $prompt = $request->prompt;
        $height = $request->height;
        $width = $request->width;
        $n_prompt = $request->n_prompt;
        $num_outputs = 1;
        // $num_inference_steps = 4;
        $num_inference_steps = $request->steps;
        // $guidance_scale = 0;
        $guidance_scale = $request->scale;
        $scheduler = 'K_EULER';
        $seed = $request->seed;

        $inputData = [];

        $inputData['prompt'] = $prompt;
        $inputData['height'] = (int)$height;
        $inputData['width'] = (int)$width;
        if($n_prompt !== null && $n_prompt !== '') {
            $inputData['negative_prompt'] = $n_prompt;
        }
        $inputData['num_outputs'] = $num_outputs;
        $inputData['num_inference_steps'] = $num_inference_steps;
        $inputData['guidance_scale'] = $guidance_scale;
        $inputData['scheduler'] = $scheduler;

        if($seed !== null) {
            $inputData['seed'] = $seed;
        }

        // CURLOPT_POSTFIELDS =>'{"version": "ac732df83cea7fff18b8472768c88ad041fa750ff7682a21affe81863cbe77e4", "input": {"text": "a photo of an astronaut riding a horse on mars"}}',
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.replicate.com/v1/predictions',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{"version": "5f24084160c9089501c1b3545d9be3c27883ae2239b6f412990e82d4a6210f8f", "input": '.json_encode($inputData).'}',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Bearer '.env('REPLICATE_API_TOKEN'),
            'Content-Type: application/json'
          ),
        ));

        $response = curl_exec($curl);
        $result = json_decode($response,true);
        curl_close($curl);

        /*echo $response;
        exit;*/

        // echo $response;
        $output = '';
        $curl1 = curl_init();
        if($result['id']){
            $output = '';
            do{
                curl_setopt_array($curl1, array(
                  CURLOPT_URL => 'https://api.replicate.com/v1/predictions/'.$result['id'],
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => '',
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => 'GET',
                  CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer '.env('REPLICATE_API_TOKEN')
                  ),
                ));

                $response1 = curl_exec($curl1);

                curl_close($curl1);
                $result1 = json_decode($response1,true);
                if($result1['status']=='failed'){
                    break;
                }
                if(isset($result1['output'])){
                    $output = $result1['output'][0];
                }
                // echo $response1;
            }while ($result1['status']!='succeeded');
        }
        if($output){
            return json_encode(['status' => true,'output'=> $output]);
        }else{
            return json_encode(['status' => false,'output'=> $output,'message' => 'this job has been failed please try again.']);
        }
        // return json_encode(['status' => true,'output'=> $output]);
    }
 public function ImgToImg(Request $request)
{
    $tokens = '$2y$10$beZC9ycZaKOyd';
    if($tokens!=$request->token){
        return json_encode(['status' => false, 'message' => 'Unauthorized access!']);
    }
    
    try {
        $client = new Client();
        $curl = curl_init();

        $prompt = $request->prompt;
        $n_prompt = $request->n_prompt;
        $num_outputs = 1;
        $num_inference_steps = 50;
        $guidance_scale = 7.5;
        $scheduler = 'EulerAncestralDiscrete';
        $seed = null;

        $inputData = [];
        $image_url = '';
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            // ✅ Base64 encode - dosya kaydetmiyoruz
            $imageData = base64_encode(file_get_contents($file->getPathname()));
            $image_url = 'data:image/jpeg;base64,' . $imageData;
        }
        
        $inputData['image'] = $image_url;
        $inputData['prompt'] = $prompt;
        if($n_prompt !== null && $n_prompt !== '') {
            $inputData['negative_prompt'] = $n_prompt;
        }
        $inputData['num_outputs'] = $num_outputs;
        $inputData['num_inference_steps'] = $num_inference_steps;
        $inputData['guidance_scale'] = 7.5;
        $inputData['upscale'] = 1;
        $inputData['strength'] = 0.5;
        $inputData['scheduler'] = $scheduler;
        $inputData['num_inference_steps'] = 30;
        if($seed !== null) {
            $inputData['seed'] = $seed;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.replicate.com/v1/predictions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1000,
            CURLOPT_TIMEOUT => 2000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"version": "0f7ba6926ca1e836e6dc64cf7e371402c9a4915851234378319f9b9b0f968fda", "input": '.json_encode($inputData).'}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.env('REPLICATE_API_TOKEN'),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $result = json_decode($response,true);
        curl_close($curl);

        $output = '';
        $curl1 = curl_init();
        if($result['id']){
            $output = '';
            do{
                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'https://api.replicate.com/v1/predictions/'.$result['id'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer '.env('REPLICATE_API_TOKEN')
                    ),
                ));

                $response1 = curl_exec($curl1);
                curl_close($curl1);
                $result1 = json_decode($response1,true);
                if($result1['status']=='failed'){
                    break;
                }
                if(isset($result1['output'])){
                    $output = $result1['output'][0];
                }
            }while ($result1['status']!='succeeded');
        }
        
        if($output){
            return json_encode(['status' => true,'output'=> $output]);
        }else{
            return json_encode(['status' => false,'output'=> $output,'message' => 'this job has been failed please try again.']);
        }
        
    } catch (\Exception $e) {
        return json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}
public function Outfit(Request $request)
{
    $tokens = '$2y$10$beZC9ycZaKOyd';
    if($tokens!=$request->token){
        return json_encode(['status' => false, 'message' => 'Unauthorized access!']);
    }
    
    try {
        $client = new Client();
        $curl = curl_init();
        
        $category = $request->category;

        $inputData = [];
        
        $human_img_url = '';
        $garm_img_url = '';
        
        if ($request->hasFile('human_img')) {
            $file = $request->file('human_img');
            $imageData = base64_encode(file_get_contents($file->getPathname()));
            $human_img_url = 'data:image/jpeg;base64,' . $imageData;
        }
        if ($request->hasFile('garm_img')) {
            $file1 = $request->file('garm_img');
            $imageData1 = base64_encode(file_get_contents($file1->getPathname()));
            $garm_img_url = 'data:image/jpeg;base64,' . $imageData1;
        }
        
        $inputData['crop'] = false;
        $inputData['seed'] = 42;
        $inputData['steps'] = 30;
        $inputData['category'] = $category;
        $inputData['force_dc'] = false;
        $inputData['garm_img'] = $garm_img_url;
        $inputData['human_img'] = $human_img_url;
        $inputData['mask_only'] = false;
        if($request->garment_des){
            $inputData['garment_des']=$request->garment_des;
        }else{
            $inputData['garment_des']='';
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.replicate.com/v1/predictions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1000,
            CURLOPT_TIMEOUT => 2000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"version": "c871bb9b046607b680449ecbae55fd8c6d945e0a1948644bf2361b3d021d3ff4", "input": '.json_encode($inputData).'}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.env('REPLICATE_API_TOKEN'),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $result = json_decode($response,true);
        curl_close($curl);

        $output = '';
        $curl1 = curl_init();
        if($result['id']){
            $output = '';
            do{
                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'https://api.replicate.com/v1/predictions/'.$result['id'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer '.env('REPLICATE_API_TOKEN')
                    ),
                ));

                $response1 = curl_exec($curl1);
                curl_close($curl1);
                $result1 = json_decode($response1,true);
                if($result1['status']=='failed'){
                    break;
                }
                if(isset($result1['output'])){
                    $output = $result1['output'];
                }
            }while ($result1['status']!='succeeded');
        }
        
        if($output){
            return json_encode(['status' => true,'output'=> $output]);
        }else{
            return json_encode(['status' => false,'output'=> $output,'message' => 'this job has been failed please try again.']);
        }
        
    } catch (\Exception $e) {
        return json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}
   public function InteriorDesign(Request $request)
{
    $tokens = '$2y$10$beZC9ycZaKOyd';
    if($tokens!=$request->token){
        return json_encode(['status' => false, 'message' => 'Unauthorized access!']);
    }
    
    try {
        $client = new Client();
        $curl = curl_init();

        $prompt = $request->prompt;
        $n_prompt = $request->n_prompt;
        $num_inference_steps = 50;
        $guidance_scale = $request->scale;
        $prompt_strength = $request->prompt_strength;
        $seed = null;

        $inputData = [];
        $image_url = '';
        
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $imageData = base64_encode(file_get_contents($file->getPathname()));
            $image_url = 'data:image/jpeg;base64,' . $imageData;
        }
        
        $inputData['image'] = $image_url;
        $inputData['prompt'] = $prompt;
        if($n_prompt !== null && $n_prompt !== '') {
            $inputData['negative_prompt'] = $n_prompt;
        }
        $inputData['num_inference_steps'] = $num_inference_steps;
        $inputData['guidance_scale'] = (int)$guidance_scale;
        $inputData['prompt_strength'] = (float)$prompt_strength;
        if($seed !== null) {
            $inputData['seed'] = $seed;
        }

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.replicate.com/v1/predictions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 1000,
            CURLOPT_TIMEOUT => 2000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"version": "76604baddc85b1b4616e1c6475eca080da339c8875bd4996705440484a6eac38", "input": '.json_encode($inputData).'}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.env('REPLICATE_API_TOKEN'),
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        $result = json_decode($response,true);
        curl_close($curl);

        $output = '';
        $curl1 = curl_init();
        if($result['id']){
            $output = '';
            do{
                curl_setopt_array($curl1, array(
                    CURLOPT_URL => 'https://api.replicate.com/v1/predictions/'.$result['id'],
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                    CURLOPT_HTTPHEADER => array(
                        'Authorization: Bearer '.env('REPLICATE_API_TOKEN')
                    ),
                ));

                $response1 = curl_exec($curl1);
                curl_close($curl1);
                $result1 = json_decode($response1,true);
                if($result1['status']=='failed'){
                    break;
                }
                if(isset($result1['output'])){
                    $output = $result1['output'];
                }
            }while ($result1['status']!='succeeded');
        }
        
        if($output){
            return json_encode(['status' => true,'output'=> $output]);
        }else{
            return json_encode(['status' => false,'output'=> $output,'message' => 'this job has been failed please try again.']);
        }
        
    } catch (\Exception $e) {
        return json_encode(['status' => false, 'message' => $e->getMessage()]);
    }
}
}
        
