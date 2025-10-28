# Hướng dẫn hoàn thiện chức năng nhận diện khuôn mặt AWS

## ✅ CHECKLIST HOÀN THÀNH

### 1. CẤU HÌNH AWS

#### A. Lambda Function
- [x] Deploy Lambda function `student-face-recognition`
- [x] Cấu hình environment variables trong Lambda
- [x] Test Lambda function
- [x] Cấp quyền IAM cho Lambda:
  - Rekognition: SearchFacesByImage
  - DynamoDB: GetItem, PutItem

#### B. DynamoDB Tables
- [x] Table `student` với key: `rekognitionId` (faceId)
- [x] Table `attendance` với keys: `exam_schedule_id`, `student_code`

#### C. Rekognition Collection
- [x] Collection name: `students`
- [x] Đã index ảnh sinh viên vào collection

### 2. CẤU HÌNH LARAVEL

#### A. Environment (.env)
```env
AWS_ACCESS_KEY_ID=AKIAZDBBPQICRJ4VJMMM
AWS_SECRET_ACCESS_KEY=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
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=ducdung-student-images
AWS_LAMBDA_FACE_RECOGNITION=student-face-recognition
```

#### B. Files Created
- [x] `app/Services/FaceRecognitionService.php` - Service gọi Lambda
- [x] `app/Http/Controllers/FaceAttendanceController.php` - Controller xử lý
- [x] Routes API đã thêm

#### C. API Endpoints
- `POST /api/attendance/face-recognition` - Nhận diện và điểm danh
- `GET /api/attendance/test-lambda` - Test kết nối Lambda

### 3. DATABASE SYNC

⚠️ **QUAN TRỌNG**: Đảm bảo data đồng bộ giữa MySQL và DynamoDB

#### DynamoDB Student Table Schema:
```json
{
  "rekognitionId": "face-id-from-rekognition",  // PRIMARY KEY
  "student_code": "2021600001",
  "full_name": "Nguyễn Văn A",
  "class_name": "CNTT1",
  "email": "student@example.com"
}
```

#### DynamoDB Attendance Table Schema:
```json
{
  "exam_schedule_id": "1",           // PARTITION KEY
  "student_code": "2021600001",      // SORT KEY
  "rekognition_result": "match",
  "confidence": 95.5,
  "face_id": "face-id-from-rekognition",
  "attendance_time": "2025-10-28T10:30:00Z",
  "created_at": "2025-10-28T10:30:00Z"
}
```

## 📋 WORKFLOW HOÀN CHỈNH

```
Frontend (Camera) 
    ↓ base64 image
API Laravel (/api/attendance/face-recognition)
    ↓ validate & invoke
AWS Lambda (student-face-recognition)
    ↓ SearchFacesByImage
AWS Rekognition Collection
    ↓ faceId + confidence
DynamoDB Student Table (get student info)
    ↓ student data
DynamoDB Attendance Table (check & save)
    ↓ attendance record
Lambda Response → Laravel
    ↓ save to MySQL
MySQL attendance_records table
    ↓ JSON response
Frontend (show result)
```

## 🚀 TESTING

### 1. Test Lambda Connection
```bash
curl http://localhost:8000/api/attendance/test-lambda
```

Expected Response:
```json
{
  "success": true,
  "message": "Lambda function is accessible"
}
```

### 2. Test Face Recognition
```javascript
// From browser console
const canvas = document.getElementById('canvas');
const imageBase64 = canvas.toDataURL('image/jpeg');

fetch('/api/attendance/face-recognition', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
  },
  body: JSON.stringify({
    image: imageBase64,
    exam_schedule_id: '1'
  })
})
.then(r => r.json())
.then(console.log);
```

## ⚙️ CẤU HÌNH BỔ SUNG

### 1. Tăng timeout cho Lambda calls (config/aws.php)
```php
<?php
return [
    'credentials' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ],
    'region' => env('AWS_DEFAULT_REGION', 'ap-southeast-1'),
    'version' => 'latest',
    'http' => [
        'timeout' => 30, // Tăng timeout lên 30 giây
        'connect_timeout' => 5,
    ],
];
```

### 2. Log configuration (config/logging.php)
Đảm bảo có channel `stack` để log AWS errors:
```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
    ],
],
```

## 🔍 TROUBLESHOOTING

### Issue 1: "Face not found"
**Nguyên nhân**: Sinh viên chưa được index vào Rekognition
**Giải pháp**: 
1. Upload ảnh sinh viên lên S3
2. Index vào Rekognition Collection với ExternalImageId = student_code
3. Lưu faceId vào DynamoDB student table

### Issue 2: "Student already attended"
**Nguyên nhân**: Đã có record trong attendance table
**Giải pháp**: Kiểm tra và xóa nếu cần test lại

### Issue 3: "Lambda timeout"
**Nguyên nhân**: Lambda function mất quá nhiều thời gian
**Giải pháp**:
1. Tăng timeout trong Lambda configuration (30s)
2. Tăng memory cho Lambda (512MB-1024MB)
3. Tối ưu code Lambda

### Issue 4: "Invalid base64 image"
**Nguyên nhân**: Format ảnh sai
**Giải pháp**: 
- Đảm bảo canvas.toDataURL('image/jpeg')
- Remove data URL prefix trong Lambda nếu có

## 📝 NEXT STEPS

### 1. Production Checklist
- [ ] Thay đổi AWS credentials thành IAM Role (bảo mật hơn)
- [ ] Enable CloudWatch logs cho Lambda
- [ ] Setup S3 bucket để lưu captured images
- [ ] Add retry logic cho Lambda calls
- [ ] Implement rate limiting
- [ ] Add monitoring & alerting

### 2. Tính năng mở rộng
- [ ] Lưu ảnh điểm danh lên S3
- [ ] Batch processing cho nhiều sinh viên
- [ ] Real-time notification
- [ ] Attendance report & analytics
- [ ] Face registration flow

### 3. Performance Optimization
- [ ] Cache Rekognition results (5 minutes)
- [ ] Async Lambda invocation cho bulk operations
- [ ] Image compression trước khi gửi
- [ ] CDN cho static assets

## 🛡️ SECURITY NOTES

1. **AWS Credentials**: Không commit vào git
2. **API Rate Limiting**: Implement để tránh abuse
3. **Image Validation**: Validate size & format
4. **CORS Configuration**: Chỉ allow domain của bạn
5. **HTTPS Only**: Bắt buộc trong production

## 📞 SUPPORT

Nếu gặp lỗi:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Lambda logs: CloudWatch Logs
3. Check API response: Browser DevTools Network tab
4. Test Lambda directly: AWS Lambda Console → Test

---
**Last Updated**: 2025-10-28
**Version**: 1.0
