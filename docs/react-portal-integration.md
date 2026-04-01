# React Portal Integration Guide

## 1) Environment

Frontend .env:

VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1

Backend .env:

FRONTEND_URLS=http://localhost:5173,http://localhost:3000

Then clear backend config cache:

php artisan config:clear

## 2) Login Flow (Bearer Token)

- Call POST /auth/login with:
  - email
  - password
  - device_name (optional)
- Save access_token in memory or localStorage.
- Add Authorization: Bearer <token> for all protected APIs.
- Call GET /auth/me to get current user and role.
- Redirect by role:
  - student -> /student
  - lecturer -> /lecturer

## 3) Suggested React Route Tree

- /login
- /student/exam-schedules
- /student/attendance-results
- /student/face-registration
- /lecturer/exam-schedules
- /lecturer/today-exams
- /lecturer/current-exam

## 4) API Endpoints for Student

- GET /student/exam-schedules
- GET /student/attendance-results
- GET /student/face-registration/window
- POST /student/face-registration/generate-upload-url
- POST /student/face-registration/confirm-upload

## 5) API Endpoints for Lecturer

- GET /lecturer/exam-schedules
- GET /lecturer/exam-schedules/today
- GET /lecturer/exam-schedules/current
- GET /lecturer/students/today-exam?student_code=...
- POST /lecturer/attendance/face-recognition
- POST /lecturer/attendance/qr-scan
- PATCH /lecturer/attendance-records/{id}

## 6) API Endpoints for Session

- GET /auth/me
- POST /auth/logout
- POST /auth/logout-all
- DELETE /auth/tokens/{tokenId}

## 7) Axios Example

import axios from 'axios';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL,
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('access_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

## 8) Minimal Auth Guard Idea

- Public routes: login
- Private routes:
  - require token
  - require role if route is role-specific
- If GET /auth/me returns 401:
  - clear token
  - redirect to /login

## 9) Security Notes

- Use HTTPS in production.
- Do not store token in plain localStorage if XSS risk is high.
- Add frontend-level logout timer if needed.
- Keep login rate limit enabled.

## 10) Postman Collection

- File: docs/postman/quanlydiemdanh-api-v1.postman_collection.json
- Import this file into Postman and run Login first to auto-set access_token.

## 11) Test Guide For Each API (Postman)

### A. Prepare before testing

- Start backend server and database.
- In Postman, import collection file at docs/postman/quanlydiemdanh-api-v1.postman_collection.json.
- Confirm collection variable baseUrl is correct, for example http://127.0.0.1:8000/api/v1.
- Prepare 2 test accounts:
  - 1 student account linked to student_code in database.
  - 1 lecturer account linked to lecturer profile and assigned at least 1 exam schedule.
- For lecturer attendance APIs, ensure exam_schedule_id is valid and exam is in active time window.

### B. Auth APIs

1. POST /auth/login
- Request body: email, password, device_name.
- Expect: 200, success true, access_token returned.
- After running, access_token is auto-saved by test script.

2. GET /auth/me
- Header: Authorization Bearer using access_token.
- Expect: 200, user object and correct role.

3. POST /auth/logout
- Header: Authorization Bearer using access_token.
- Expect: 200.
- Re-test GET /auth/me with same token should fail 401.

4. POST /auth/logout-all
- Header: Authorization Bearer using access_token.
- Expect: 200 and all tokens of current user are revoked.

5. DELETE /auth/tokens/{tokenId}
- Header: Authorization Bearer using access_token.
- Set variable token_id to token that belongs to current user.
- Expect: 200 when token exists and is owned by user, 404 otherwise.

### C. Student APIs

Important: Login with student account first.

1. GET /student/exam-schedules
- Optional query: limit, q.
- Expect: 200 with paginated exam schedules for current student.

2. GET /student/attendance-results
- Optional query: limit.
- Expect: 200 with paginated attendance results for current student.

3. GET /student/face-registration/window
- Expect: 200.
- If window open: can_register true.
- If no active window: can_register false.

4. POST /student/face-registration/generate-upload-url
- Body: file_name must match student_code, file_type is image/jpeg or image/png.
- Expect: 200 with upload_url and s3_key.
- Typical fail cases:
  - 403 when admin has not opened window.
  - 422 when filename is invalid or not matching student_code.

5. POST /student/face-registration/confirm-upload
- Body: file_name same as uploaded file.
- Expect: 200 and success message when file naming is correct.
- Typical fail cases:
  - 403 when window closed.
  - 422 when file_name does not match student_code.

### D. Lecturer APIs

Important: Login with lecturer account first.

1. GET /lecturer/exam-schedules
- Optional query: limit, q, date.
- Expect: 200 with paginated invigilation schedules.

2. GET /lecturer/exam-schedules/today
- Expect: 200.
- If no exam today: success false with data [].
- If has exams: success true and count > 0.

3. GET /lecturer/exam-schedules/current
- Expect:
  - 200 with current/upcoming exam data, or
  - 404 when no current/upcoming exam.

4. GET /lecturer/students/today-exam?student_code=...
- Query param student_code is required.
- Expect: 200 with has_exam true/false.

5. POST /lecturer/attendance/face-recognition
- Body: exam_schedule_id, image (base64), commit (true/false).
- Expect: 200 when recognized and attendance updated (commit true).
- Typical fail cases:
  - 403 when lecturer is not assigned to exam.
  - 422 when outside exam attendance time window.
  - 404 when student is not in attendance list.

6. POST /lecturer/attendance/qr-scan
- Body: exam_schedule_id, qr_content, commit (true/false).
- Expect: 200 when qr is valid and attendance updated.
- Typical fail cases similar to face-recognition.

7. PATCH /lecturer/attendance-records/{id}
- Body can include: attendance_time, rekognition_result, confidence, attendance_method.
- attendance_method supports: manual, face, qr_code.
- Expect: 200 when update is valid and within exam time window.
- Typical fail cases:
  - 403 when lecturer has no permission for exam.
  - 422 when outside exam attendance time window.

### E. Recommended full regression flow

1. Login as lecturer.
2. Run My Invigilation Schedule.
3. Run Today Exams and pick valid exam_schedule_id.
4. Run Student Today Exam with valid student_code.
5. Run Attendance QR Scan with commit false, then commit true.
6. Run Manual Update Attendance Record for the same record.
7. Run auth/me to confirm token still valid.
8. Logout.

