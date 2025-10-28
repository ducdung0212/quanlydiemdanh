<?php

namespace App\Services;

use Aws\Lambda\LambdaClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    protected $lambdaClient;
    protected $functionName;

    public function __construct()
    {
        $this->lambdaClient = new LambdaClient([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $this->functionName = env('AWS_LAMBDA_FACE_RECOGNITION', 'student-face-recognition');
    }

    /**
     * Xác thực khuôn mặt sinh viên
     * 
     * @param string $imageBase64 - Ảnh đã encode base64
     * @param string $examScheduleId - ID ca thi
     * @return array
     */
    public function authenticateFace(string $imageBase64, string $examScheduleId): array
    {
        try {
            // Prepare payload
            $payload = [
                'image' => $imageBase64,
                'examScheduleId' => $examScheduleId
            ];

            Log::info('Calling Lambda function', [
                'function' => $this->functionName,
                'exam_schedule_id' => $examScheduleId
            ]);

            // Invoke Lambda function
            $result = $this->lambdaClient->invoke([
                'FunctionName' => $this->functionName,
                'InvocationType' => 'RequestResponse',
                'Payload' => json_encode($payload),
            ]);

            // Parse response
            $responsePayload = json_decode($result->get('Payload')->getContents(), true);

            Log::info('Lambda response received', [
                'status_code' => $responsePayload['statusCode'] ?? null
            ]);

            // Handle Lambda response
            if (isset($responsePayload['statusCode'])) {
                $statusCode = $responsePayload['statusCode'];
                $body = json_decode($responsePayload['body'] ?? '{}', true);

                if ($statusCode === 200 && $body['success'] ?? false) {
                    return [
                        'success' => true,
                        'data' => $body['data'] ?? [],
                        'message' => $body['message'] ?? 'Authentication successful'
                    ];
                }

                return [
                    'success' => false,
                    'message' => $body['message'] ?? 'Authentication failed',
                    'status_code' => $statusCode
                ];
            }

            // Lambda error
            return [
                'success' => false,
                'message' => 'Invalid Lambda response format'
            ];

        } catch (AwsException $e) {
            Log::error('AWS Lambda error', [
                'error' => $e->getMessage(),
                'code' => $e->getAwsErrorCode()
            ]);

            return [
                'success' => false,
                'message' => 'AWS service error: ' . $e->getMessage()
            ];

        } catch (\Exception $e) {
            Log::error('Face recognition error', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'System error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Test connection to Lambda
     */
    public function testConnection(): array
    {
        try {
            $result = $this->lambdaClient->invoke([
                'FunctionName' => $this->functionName,
                'InvocationType' => 'DryRun',
            ]);

            return [
                'success' => true,
                'message' => 'Lambda function is accessible'
            ];

        } catch (AwsException $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
