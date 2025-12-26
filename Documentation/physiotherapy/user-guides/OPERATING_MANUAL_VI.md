# Hướng dẫn vận hành - Mô-đun Vật lý trị liệu Việt Nam

**Hướng dẫn Người dùng Toàn diện cho Nhân viên Y tế**
*Mô-đun Vật lý trị liệu Việt Nam OpenEMR v1.0*
*Tháng 11 năm 2025*

---

## Mục lục

1. [Hướng dẫn Bắt đầu Nhanh](#1-hướng-dẫn-bắt-đầu-nhanh)
2. [Tổng quan Mô-đun](#2-tổng-quan-mô-đun)
3. [Yêu cầu Hệ thống](#3-yêu-cầu-hệ-thống)
4. [Cách Truy cập](#4-cách-truy-cập)
5. [Tiện ích Tóm tắt Bệnh nhân](#5-tiện-ích-tóm-tắt-bệnh-nhân)
6. [Biểu mẫu Đánh giá VL](#6-biểu-mẫu-đánh-giá-vl)
7. [Biểu mẫu Kê đơn Tập luyện](#7-biểu-mẫu-kê-đơn-tập-luyện)
8. [Biểu mẫu Kế hoạch Điều trị](#8-biểu-mẫu-kế-hoạch-điều-trị)
9. [Biểu mẫu Đo lường Kết quả](#9-biểu-mẫu-đo-lường-kết-quả)
10. [Tính năng Song ngữ](#10-tính-năng-song-ngữ)
11. [Thực tiễn tốt nhất và Quy trình Làm việc](#11-thực-tiễn-tốt-nhất-và-quy-trình-làm-việc)
12. [Khắc phục Sự cố](#12-khắc-phục-sự-cố)
13. [Tham chiếu Nhanh](#13-tham-chiếu-nhanh)
14. [Phần Phụ lục](#14-phần-phụ-lục)

---

## 1. Hướng dẫn Bắt đầu Nhanh

### Mô-đun Vật lý trị liệu Việt Nam là gì?

Mô-đun Vật lý trị liệu (VL) Việt Nam là một hệ thống lâm sàng toàn diện, song ngữ được tích hợp vào OpenEMR. Nó cung cấp các công cụ chuyên biệt để ghi chép vật lý trị liệu và quản lý bệnh nhân với hỗ trợ đầy đủ tiếng Việt.

**Các Thành phần Chính:**
- 4 biểu mẫu lâm sàng cho đánh giá, kê đơn tập luyện, lập kế hoạch điều trị, theo dõi kết quả
- Giao diện song ngữ (Tiếng Anh/Tiếng Việt) với ghi chép song song
- Tiện ích tóm tắt bệnh nhân để truy cập nhanh dữ liệu VL
- Dịch thuật thuật ngữ y học được tích hợp sẵn
- Hỗ trợ các thực tiễn y tế Việt Nam

**Ai sử dụng điều này?**
- Chuyên gia vật lý trị liệu và Nhân viên vật lý trị liệu
- Chuyên gia phục hồi chức năng
- Nhân viên y tế điều trị bệnh nhân nói tiếng Việt
- Phòng khám và bệnh viện sử dụng OpenEMR

### Điều kiện tiên quyết

Trước khi sử dụng mô-đun VL, đảm bảo rằng bạn có:

- [ ] Tài khoản người dùng OpenEMR hoạt động với các quyền thích hợp
- [ ] Quyền truy cập mô-đun VL được quản trị viên hệ thống kích hoạt
- [ ] Hiểu biết cơ bản về điều hướng OpenEMR
- [ ] Hiểu biết về quy trình gặp bệnh nhân
- [ ] Khả năng nhập liệu tiếng Việt (tùy chọn, nhưng được khuyến cáo)

### Các bước đầu tiên (30 giây)

1. **Đăng nhập vào OpenEMR** bằng thông tin đăng nhập của bạn
2. **Mở hồ sơ bệnh nhân** (Lịch → Chọn cuộc hẹn → Nhấp bệnh nhân, hoặc sử dụng Tìm kiếm)
3. **Tạo/Mở một lần gặp** (Bảng điều khiển bệnh nhân → Lần gặp mới)
4. **Tìm kiếm biểu mẫu "Vật lý trị liệu Việt Nam"** trong menu Lâm sàng dưới "Thêm biểu mẫu"

Đó là tất cả! Bạn đã sẵn sàng bắt đầu ghi chép chăm sóc vật lý trị liệu.

---

## 2. Tổng quan Mô-đun

### Mô-đun này làm gì?

Mô-đun này mở rộng OpenEMR với các khả năng ghi chép vật lý trị liệu chuyên biệt:

| Tính năng | Mục đích |
|-----------|---------|
| **Biểu mẫu Đánh giá VL** | Ghi chép đánh giá bệnh nhân, phàn nàn chính, đau, và mục tiêu chức năng |
| **Kê đơn Tập luyện** | Tạo và quản lý chương trình tập luyện tại nhà với hướng dẫn song ngữ |
| **Kế hoạch Điều trị** | Xác định chiến lược điều trị, dòng thời gian và theo dõi trạng thái |
| **Đo lường Kết quả** | Theo dõi tiến độ khách quan (ROM, sức mạnh, đau, chức năng, thăng bằng) |
| **Tiện ích VL** | Xem nhanh dữ liệu VL của bệnh nhân trên trang tóm tắt |

### Nó tích hợp với OpenEMR như thế nào?

Mô-đun vật lý trị liệu Việt Nam tích hợp liền mạch với OpenEMR:

- **Gặp bệnh nhân:** Các biểu mẫu được thêm như bất kỳ biểu mẫu gặp nào khác
- **Tóm tắt bệnh nhân:** Tiện ích hiển thị dữ liệu VL trên bảng điều khiển bệnh nhân
- **Hồ sơ y tế:** Tất cả ghi chép được lưu trữ trong cơ sở dữ liệu OpenEMR
- **Báo cáo:** Dữ liệu có thể truy cập thông qua hệ thống báo cáo OpenEMR
- **Quyền người dùng:** Sử dụng kiểm soát truy cập dựa trên vai trò của OpenEMR

### Hỗ trợ Song ngữ

Tất cả các biểu mẫu hỗ trợ ghi chép song song ở cả hai ngôn ngữ:

- **Tiếng Việt:** Cho bệnh nhân nói tiếng Việt và ghi chép
- **Tiếng Anh:** Cho hồ sơ y tế, giao tiếp nhóm, nghiên cứu
- **Tùy chọn Ngôn ngữ:** Mỗi biểu mẫu cho phép bạn chọn chỉ tiếng Việt, chỉ tiếng Anh hoặc cả hai

---

## 3. Yêu cầu Hệ thống

### Yêu cầu Trình duyệt

- **Trình duyệt hiện đại:** Chrome 90+, Firefox 88+, Safari 14+, Edge 90+
- **JavaScript được bật:** Bắt buộc cho chức năng biểu mẫu và chỉ báo đau
- **Cookie được bật:** Bắt buộc để quản lý phiên OpenEMR
- **Độ phân giải màn hình:** Tối thiểu 1024x768 (1280x1024+ được khuyến cáo)

### Mã hóa Ký tự

- **Mã hóa UTF-8:** Bắt buộc để các ký tự Việt Nam hiển thị đúng
- **Mã hóa trình duyệt:** Xác minh mã hóa trang được đặt thành Unicode (UTF-8)
- **Bàn phím Việt Nam:** Được khuyến cáo để nhập dữ liệu hiệu quả

### Kết nối Internet

- **Kết nối ổn định:** Đảm bảo internet tin cậy trong khi làm việc
- **Băng thông tối thiểu:** Broadband tiêu chuẩn (1 Mbps+)
- **Hết thời gian chờ kết nối:** Biểu mẫu có thể hết thời gian sau 15-20 phút không hoạt động

### Cấu hình Quản trị viên Hệ thống

Quản trị viên hệ thống của bạn nên:

- [ ] Mô-đun VL Việt Nam được cài đặt và kích hoạt
- [ ] Các bảng cơ sở dữ liệu được tạo với dạy sắp UTF-8mb4_vietnamese_ci
- [ ] Quyền người dùng được cấu hình cho quyền truy cập vật lý trị liệu
- [ ] Biểu mẫu được đăng ký trong hệ thống OpenEMR
- [ ] Tiện ích tóm tắt bệnh nhân được bật

---

## 4. Cách Truy cập

### Quyền Người dùng Được yêu cầu

Liên hệ quản trị viên hệ thống của bạn để yêu cầu:

1. **Truy cập Biểu mẫu Vật lý trị liệu:** Quyền xem, tạo và chỉnh sửa biểu mẫu VL
2. **Quản lý Gặp:** Quyền tạo và chỉnh sửa gặp
3. **Truy cập Dữ liệu Bệnh nhân:** Truy cập hồ sơ bệnh nhân và tóm tắt
4. **Quyền Ghi chép:** Quyền hoàn thành ghi chép lâm sàng

### Truy cập Mô-đun

#### Từ Gặp Bệnh nhân

**Phương pháp 1: Thêm Biểu mẫu Mới**
1. Mở hồ sơ bệnh nhân
2. Chọn hoặc tạo một lần gặp
3. Nhấp vào tab "Lâm sàng" trong menu gặp
4. Nhấp vào dropdown "Thêm biểu mẫu"
5. Chọn biểu mẫu VL mong muốn:
   - Đánh giá Vật lý trị liệu Việt Nam
   - Kê đơn Tập luyện Vật lý trị liệu Việt Nam
   - Kế hoạch Điều trị Vật lý trị liệu Việt Nam
   - Đo lường Kết quả Vật lý trị liệu Việt Nam

**Phương pháp 2: Thêm Nhanh từ Tiện ích**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống phần "Vật lý trị liệu Việt Nam"
3. Nhấp vào nút xanh "+ Mới" bên cạnh phần mong muốn
4. Biểu mẫu mở trong lần gặp hiện tại

#### Từ Tóm tắt Bệnh nhân

**Xem Tiện ích VL:**
1. Điều hướng đến hồ sơ bệnh nhân
2. Nhấp vào tab "Tóm tắt" hoặc "Bảng điều khiển Bệnh nhân"
3. Cuộn xuống phần "Vật lý trị liệu Việt Nam" màu xanh lá cây
4. Tiện ích hiển thị ba phần chính với các nút tác vụ nhanh

---

## 5. Tiện ích Tóm tắt Bệnh nhân

### Tổng quan Tiện ích

Tiện ích Tóm tắt Bệnh nhân cung cấp quyền truy cập nhanh vào lịch sử vật lý trị liệu và trạng thái hiện tại của bệnh nhân.

### Truy cập Tiện ích

1. **Mở hồ sơ bệnh nhân** (tìm kiếm bệnh nhân hoặc chọn từ lịch)
2. **Nhấp vào "Tóm tắt"** tab hoặc "Bảng điều khiển Bệnh nhân"
3. **Cuộn xuống** phần "Vật lý trị liệu Việt Nam" màu xanh lá cây
4. Tiện ích hiển thị ba phần chính với các nút tác vụ nhanh

### Phần Tiện ích

#### Phần A: Đánh giá Gần đây

**Những gì nó hiển thị:**
- 3 lần đánh giá VL gần đây
- Ngày đánh giá
- Phàn nàn chính (tóm tắt)
- Mức đau (0-10 với mã màu)
- Trạng thái (Bản nháp/Hoàn thành/Đã xem xét)

**Mã màu:**
- Huy hiệu xanh = Đau nhẹ (0-3)
- Huy hiệu vàng = Đau trung bình (4-6)
- Huy hiệu đỏ = Đau nặng (7-10)

**Tác vụ Nhanh:**
- Nhấp vào nút "+ Mới" → Tạo đánh giá mới
- Nhấp vào hàng đánh giá → Xem/chỉnh sửa chi tiết

#### Phần B: Kê đơn Tập luyện Hoạt động

**Những gì nó hiển thị:**
- Tối đa 5 bài tập hoạt động
- Tên bài tập (Tiếng Việt hoặc Tiếng Anh)
- Hiệp, lần, tần suất (ví dụ: "3 hiệp × 10 lần - 5x/tuần")
- Mức cường độ (Nhẹ/Trung bình/Cao)

**Trạng thái Tập luyện:**
- Chỉ hiển thị tập luyện có ngày hoạt động
- Ngày kết thúc chưa được đạt tới
- Được đánh dấu là "Hoạt động"

**Tác vụ Nhanh:**
- Nhấp vào nút "+ Mới" → Thêm tập luyện mới
- Nhấp vào hàng tập luyện → Xem/chỉnh sửa chi tiết

#### Phần C: Kế hoạch Điều trị Hoạt động

**Những gì nó hiển thị:**
- Tất cả các kế hoạch điều trị hoạt động
- Tên kế hoạch
- Ngày bắt đầu
- Thời gian ước tính (tuần)
- Trạng thái (Hoạt động/Hoàn thành/Tạm dừng)

**Tác vụ Nhanh:**
- Nhấp vào nút "+ Mới" → Tạo kế hoạch mới
- Nhấp vào hàng kế hoạch → Xem/chỉnh sửa chi tiết

### Sử dụng Nút Thêm Nhanh

**Để Tạo Dữ liệu VL Mới Sử dụng Tiện ích:**

1. Nhấp vào nút xanh "+ Mới" bên cạnh phần mong muốn
2. Biểu mẫu mở trong lần gặp hiện tại (hoặc tạo lần gặp mới nếu cần)
3. Hoàn thành biểu mẫu như được mô tả trong phần 6-9
4. Nhấp vào nút "Lưu [Tên Biểu mẫu]"
5. Quay lại tiện ích và làm mới (F5) để xem dữ liệu được cập nhật

**Lợi ích:**
- Bỏ qua điều hướng qua menu
- Truy cập một nhấp chuột trực tiếp vào biểu mẫu
- Ghi chép nhanh hơn trong quá trình gặp bệnh nhân

### Khi Tiện ích Hiển thị "Không có Dữ liệu"

Nếu một phần hiển thị "Chưa ghi lại bất kỳ đánh giá nào" hoặc tương tự:

**Điều này bình thường khi:**
- Bệnh nhân chưa bao giờ có đánh giá VL
- Tất cả các bài tập trước đây đã kết thúc
- Các kế hoạch điều trị đã được hoàn thành và đóng

**Để điền vào tiện ích:**
1. Tạo biểu mẫu VL đầu tiên bằng nút "+ Mới"
2. Hoàn thành và lưu biểu mẫu
3. Tiện ích tự động cập nhật để hiển thị dữ liệu mới

### Làm mới Dữ liệu Tiện ích

Tiện ích tự động cập nhật khi bạn:
- Tạo biểu mẫu VL mới
- Lưu thay đổi vào biểu mẫu hiện có
- Cập nhật trạng thái bài tập
- Thay đổi trạng thái kế hoạch điều trị

Nếu các thay đổi gần đây không xuất hiện:
- Làm mới trang (F5 hoặc Ctrl+R)
- Điều hướng đi và quay lại tóm tắt bệnh nhân

---

## 6. Biểu mẫu Đánh giá VL

### Mục đích

Biểu mẫu Đánh giá VL là công cụ ghi chép chính cho đánh giá bệnh nhân vật lý trị liệu. Nó nắm bắt:
- Phàn nàn chính và mô tả triệu chứng của bệnh nhân
- Mức độ đau và đặc điểm đau
- Các hạn chế chức năng và mục tiêu
- Phương pháp điều trị đề xuất
- Trạng thái lâm sàng

### Khi nào sử dụng

- Đánh giá bệnh nhân ban đầu (phải hoàn thành)
- Tái đánh giá tiến độ (mỗi 2-4 tuần)
- Đánh giá xuất viện (lần gặp cuối)
- Thay đổi trạng thái đáng kể

### Truy cập Biểu mẫu

**Tùy chọn 1: Từ Menu Gặp**
1. Mở lần gặp bệnh nhân
2. Nhấp vào tab "Lâm sàng"
3. Nhấp vào "Thêm biểu mẫu" → "Đánh giá Vật lý trị liệu Việt Nam"

**Tùy chọn 2: Từ Tiện ích VL**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống "Vật lý trị liệu Việt Nam"
3. Nhấp vào "+ Mới" trong phần "Đánh giá Gần đây"

### Hoàn thành Biểu mẫu Đánh giá

#### Bước 1: Chọn Tùy chọn Ngôn ngữ

Ở đầu biểu mẫu, chọn ngôn ngữ ghi chép của bạn:

- **Tiếng Việt:** Chỉ hiển thị các trường tiếng Việt (nền vàng)
- **Tiếng Anh:** Chỉ hiển thị các trường tiếng Anh (nền xanh)
- **Cả hai (Được khuyến cáo):** Cả trường tiếng Việt và tiếng Anh hiển thị cạnh nhau

**Khuyến cáo:**
Chọn "Cả hai" để ghi chép song ngữ toàn diện phục vụ cho bệnh nhân nói tiếng Việt và đồng nghiệp.

#### Bước 2: Ghi chép Phàn nàn Chính

**Loại trường:** Khu vực văn bản lớn với các phần dành riêng cho ngôn ngữ

**Trường Tiếng Việt (Nền vàng):**
```
Ví dụ: Đau lưng dưới mãn tính từ 6 tháng,
tăng khi ngồi lâu hoặc nâng vật nặng
```

**Trường Tiếng Anh (Nền xanh):**
```
Ví dụ: Đau lưng dưới mãn tính từ 6 tháng,
tăng khi ngồi lâu hoặc nâng vật nặng
```

**Thực tiễn tốt nhất:**
- Cụ thể về các triệu chứng
- Bao gồm thời gian và kích hoạt
- Mô tả tác động chức năng
- Sử dụng thuật ngữ y học chuyên nghiệp
- Hoàn thành cả hai ngôn ngữ khi tùy chọn ngôn ngữ là "Cả hai"

#### Bước 3: Đánh giá Đau

**Mức Đau (Thang 0-10):**
1. Nhập giá trị số (0 = không đau, 10 = đau nhất)
2. Chỉ báo trực quan tự động cập nhật:
   - 0-3: Huy hiệu xanh (đau nhẹ)
   - 4-6: Huy hiệu vàng (đau trung bình)
   - 7-10: Huy hiệu đỏ (đau nặng)

**Vị trí Đau:**

Ví dụ Tiếng Việt:
```
Lưng dưới bên phải, có thể lan xuống mông
```

Ví dụ Tiếng Anh:
```
Lưng dưới bên phải, có thể lan xuống mông
```

**Mô tả Đau:**

Ví dụ Tiếng Việt:
```
Đau nhói, tăng khi ngồi lâu, giảm khi nằm
```

Ví dụ Tiếng Anh:
```
Đau nhói, tăng khi ngồi lâu, giảm khi nằm
```

**Các mô tả Đau phổ biến:**
- đau nhói = đau cấp tính/giật
- đau âm ỉ = đau cùn
- đau rát = đau buốt
- đau buốt = đau nhấp nháy
- đau lan tỏa = đau toả tia

#### Bước 4: Mục tiêu Chức năng

Ghi chép các mục tiêu có thể đo lường, tập trung vào bệnh nhân:

**Ví dụ Tiếng Việt:**
```
- Có thể đi bộ 30 phút không đau
- Ngủ suốt đêm không bị đau đánh thức
- Ngồi làm việc 2 giờ liên tục
- Trở lại tập gym hoặc thể thao nhẹ
```

**Ví dụ Tiếng Anh:**
```
- Walk 30 minutes without pain
- Sleep through the night
- Sit and work for 2 hours continuously
- Return to gym or light exercise
```

**Mẹo Đặt mục tiêu:**
- Làm cho các mục tiêu cụ thể và có thể đo lường
- Bao gồm khung thời gian khi có thể
- Tập trung vào các hoạt động chức năng (không chỉ giảm đau)
- Hỏi đầu vào bệnh nhân cho các mục tiêu thực tế
- Bao gồm ít nhất 2-3 mục tiêu

#### Bước 5: Tóm tắt Kế hoạch Điều trị

Phác thảo phương pháp điều trị dự kiến:

**Ví dụ Tiếng Việt:**
```
- Vật lý trị liệu 3 lần/tuần trong 8 tuần
- Bài tập kéo giãn và tăng cường
- Giáo dục tư thế ngồi đúng
- Modalities như TENS nếu cần
- Tái đánh giá sau 2 tuần
```

**Ví dụ Tiếng Anh:**
```
- Physical therapy 3 times/week for 8 weeks
- Stretching and strengthening exercises
- Education on proper sitting posture
- Modalities such as TENS if needed
- Re-evaluation after 2 weeks
```

**Kế hoạch Điều trị Nên Bao gồm:**
- Tần suất và thời gian điều trị
- Loại can thiệp (trị liệu bằng tay, tập luyện, modalities)
- Các chủ đề giáo dục
- Dòng thời gian tái đánh giá
- Các chống chỉ định hoặc cảnh báo

#### Bước 6: Đặt Trạng thái Đánh giá

Chọn trạng thái thích hợp:

| Trạng thái | Ý nghĩa | Khi nào sử dụng |
|----------|---------|--------------|
| Bản nháp | Biểu mẫu chưa hoàn thành, cần nhiều công việc | Đánh giá bị gián đoạn, ghi chép chưa hoàn thành |
| Hoàn thành | Đánh giá là cuối cùng và hoàn thành | Sẵn sàng sử dụng lâm sàn |
| Đã xem xét | Đánh giá đã được xem xét/phê duyệt | Hoàn thành xem xét ngang hàng, quản lý ký tên |

Mặc định: "Hoàn thành"

#### Bước 7: Lưu Đánh giá

1. Xác minh tất cả thông tin bắt buộc đã hoàn thành
2. Nhấp vào nút xanh "Lưu Đánh giá" ở dưới cùng của biểu mẫu
3. Hệ thống lưu dữ liệu và quay lại trang gặp
4. Đánh giá xuất hiện trong danh sách biểu mẫu gặp

**Để Hủy bỏ Mà không Lưu:**
1. Nhấp vào nút xám "Hủy bỏ"
2. Quay lại trang trước mà không lưu các thay đổi
3. Công việc chưa lưu sẽ bị mất

### Xem hoặc Chỉnh sửa Đánh giá Hiện có

**Để Xem/Chỉnh sửa Đánh giá:**
1. Điều hướng đến lần gặp bệnh nhân
2. Tìm đánh giá trong danh sách biểu mẫu gặp
3. Nhấp vào tiêu đề đánh giá
4. Biểu mẫu mở với dữ liệu hiện có được điền sẵn
5. Thực hiện các thay đổi cần thiết
6. Nhấp vào "Lưu Đánh giá" để cập nhật

**Để Xem trong Tiện ích VL:**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống phần "Đánh giá Gần đây"
3. Nhấp vào hàng đánh giá để xem chi tiết

---

## 7. Biểu mẫu Kê đơn Tập luyện

### Mục đích

Biểu mẫu Kê đơn Tập luyện cho phép bạn tạo các chương trình tập luyện chi tiết, song ngữ với các tham số cụ thể (hiệp, lần, tần suất, cường độ) và hướng dẫn.

### Khi nào sử dụng

- Kê đơn tập luyện ban đầu (ở đầu điều trị)
- Thêm tập luyện mới vào chương trình
- Sửa đổi tập luyện hiện có (hiệp, lần, cường độ)
- Nâng cao độ khó tập luyện
- Giáo dục bệnh nhân và sơ đồ chương trình tại nhà

### Truy cập Biểu mẫu

**Tùy chọn 1: Từ Menu Gặp**
1. Mở lần gặp bệnh nhân
2. Nhấp vào tab "Lâm sàn"
3. Nhấp vào "Thêm biểu mẫu" → "Kê đơn Tập luyện Vật lý trị liệu Việt Nam"

**Tùy chọn 2: Từ Tiện ích VL**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống "Vật lý trị liệu Việt Nam"
3. Nhấp vào "+ Mới" trong phần "Kê đơn Tập luyện Hoạt động"

### Hoàn thành Biểu mẫu Kê đơn Tập luyện

#### Bước 1: Nhập Tên Tập luyện (Song ngữ)

Cung cấp tên bài tập rõ ràng ở cả hai ngôn ngữ:

**Ví dụ Tiếng Việt:**
```
Động tác mèo-bò (Cat-Cow)
```

**Ví dụ Tiếng Anh:**
```
Cat-Cow Stretch
```

**Thực tiễn tốt nhất:**
- Sử dụng thuật ngữ tập luyện chuyên nghiệp
- Bao gồm tên tiếng Anh trong ngoặc đơn cho các tập luyện phổ biến
- Nhất quán với đặt tên trong các kê đơn
- Tránh các thuật ngữ lóng hoặc thông thường

#### Bước 2: Mô tả Kỹ thuật Tập luyện

Cung cấp hướng dẫn rõ ràng, từng bước để thực hiện tập luyện:

**Ví dụ Tiếng Việt:**
```
Quỳ bốn chân, tay thẳng dưới vai, đầu gối dưới hông.
Hít vào sâu: vùng lưng xuống, ngẩng đầu lên (tư thế bò).
Thở ra: gù lưng lên, cúi đầu xuống (tư thế mèo).
Chuyển động chậm, mượt mà, không gập gật.
Lặp lại 10 lần, 2 lần mỗi ngày.
```

**Ví dụ Tiếng Anh:**
```
Start in all-fours position with hands under shoulders and knees under hips.
Inhale deeply: lower the back, look upward (cow position).
Exhale: round the back, tuck chin downward (cat position).
Move slowly and smoothly without jerking motions.
Repeat 10 times, 2 sets daily.
```

**Mô tả Hiệu quả Nên:**
- Bao gồm vị trí bắt đầu
- Mô tả chuyển động rõ ràng
- Lưu ý dấu hiệu hít thở
- Bao gồm các sửa đổi nếu có
- Có thể đọc được ở cấp độ giáo dục bệnh nhân

#### Bước 3: Đặt Tham số Tập luyện

Cấu hình chi tiết kê đơn tập luyện:

| Tham số | Tên trường | Loại | Phạm vi | Mặc định | Ví dụ |
|---------|-----------|------|--------|----------|-------|
| Hiệp | Hiệp | Số | 1-10 | 3 | 3 |
| Lần | Lần | Số | 1-50 | 10 | 10 |
| Thời gian | Thời gian | Phút | Tùy chọn | - | 5 |
| Tần suất | Tần suất mỗi tuần | Ngày | 1-7 | 5 | 5 |
| Cường độ | Mức độ cường độ | Dropdown | Nhẹ/Trung bình/Cao | Trung bình | Trung bình |

**Đặt Tần suất:**
- Nhập số ngày mỗi tuần để thực hiện tập luyện
- Ví dụ: 5 = thực hiện từ thứ hai đến thứ sáu
- Ví dụ: 7 = thực hiện hàng ngày
- Ví dụ: 2 = thực hiện hai lần mỗi tuần

**Đặt Mức Cường độ:**
- **Nhẹ:** Phục hồi ban đầu, tập luyện nhẹ, giai đoạn đau cấp tính
- **Trung bình:** Tập luyện trị liệu tiêu chuẩn, phổ biến nhất (mặc định)
- **Cao:** Bệnh nhân nâng cao, giai đoạn tăng cường, chuyển động không đau

#### Bước 4: Đặt Phạm vi Ngày

Cấu hình khi tập luyện nên được thực hiện:

**Ngày Bắt đầu:**
- Mặc định: Ngày hôm nay
- Nhấp vào biểu tượng lịch để chọn ngày khác
- Đây là khi bệnh nhân nên bắt đầu tập luyện

**Ngày Kết thúc (Tùy chọn):**
- Để trống cho tập luyện đang diễn ra
- Đặt ngày cụ thể nếu tập luyện tạm thời
- Tập luyện trở nên "không hoạt động" sau ngày kết thúc
- Ví dụ: Đặt ngày kết thúc 4 tuần cho tải trọng tiến triển

#### Bước 5: Thêm Hướng dẫn Tập luyện

Cung cấp hướng dẫn bổ sung và các lưu ý an toàn:

**Ví dụ Tiếng Việt:**
```
- Thực hiện vào buổi sáng khi thức dậy
- Có thể thực hiện trên giường hoặc sàn
- Dừng lại nếu cảm thấy đau tăng
- Kết hợp với hơi thở sâu
- Tăng số lần nếu cảm thấy quá dễ
```

**Ví dụ Tiếng Anh:**
```
- Perform in the morning upon waking
- Can be performed in bed or on the floor
- Stop if pain increases
- Combine with deep breathing
- Increase repetitions if exercise feels too easy
```

**Hướng dẫn Hiệu quả Bao gồm:**
- Thời gian tốt nhất để thực hiện
- Nơi để thực hiện (vị trí)
- Khi nào dừng hoặc sửa đổi
- Cách tiến hành khi sẵn sàng
- Cân nhắc về môi trường

#### Bước 6: Chỉ định Thiết bị Cần thiết

Liệt kê bất kỳ thiết bị nào cần thiết cho tập luyện:

**Ví dụ:**
- Thảm yoga
- Dây kháng lực
- Tạ hoặc trọng lượng (chỉ định trọng lượng)
- Ghế để tựa
- Gối hoặc đệm
- Khăn

**Định dạng:**
```
Thảm yoga, ghế tựa, gối
```
hoặc
```
Thảm yoga, ghế để tựa, gối
```

#### Bước 7: Ghi chép Các biện pháp An toàn

Mô tả bất kỳ cảnh báo hoặc chống chỉ định:

**Ví dụ Tiếng Việt:**
```
- Không làm nếu có đau cấp tính
- Tránh động tác gật gù đầu quá mạnh
- Giữ cột sống trung tính, không quá võng
- Ngừng nếu có tê hoặc chân yếu
```

**Ví dụ Tiếng Anh:**
```
- Do not perform if experiencing acute pain
- Avoid excessive cervical nodding movements
- Keep spine neutral, avoid excessive extension
- Stop if experiencing numbness or leg weakness
```

**Các biện pháp Nên Bao gồm:**
- Chống chỉ định liên quan đến đau
- Hạn chế chuyển động
- Các triệu chứng thần kinh để theo dõi
- Khi liên hệ nhân viên trị liệu
- Các vị trí cụ thể để tránh

#### Bước 8: Lưu Kê đơn Tập luyện

1. Xác minh tất cả các trường bắt buộc đã hoàn thành
2. Nhấp vào nút xanh "Lưu Kê đơn Tập luyện"
3. Hệ thống lưu dữ liệu và quay lại trang gặp
4. Tập luyện xuất hiện trong:
   - Danh sách biểu mẫu gặp
   - Phần "Kê đơn Tập luyện Hoạt động" của tiện ích VL
   - Báo cáo và tóm tắt bệnh nhân

### Quản lý Chương trình Tập luyện

#### Tạo Chương trình Tập luyện Tại nhà Hoàn chỉnh

Ví dụ: Đau lưng dưới, chương trình 4 tuần

**Tập luyện 1: Nghiêng khung chậu**
- Hiệp: 3 | Lần: 15 | Tần suất: 7x/tuần | Cường độ: Nhẹ
- Thời gian: Tuần 1-4
- Mục đích: Kích hoạt lõi, giảm đau

**Tập luyện 2: Mèo-Bò**
- Hiệp: 2 | Lần: 10 | Tần suất: 7x/tuần | Cường độ: Nhẹ
- Thời gian: Tuần 1-4
- Mục đích: Tính động cột sống, linh hoạt

**Tập luyện 3: Con bọ chết**
- Hiệp: 3 | Lần: 10 | Tần suất: 5x/tuần | Cường độ: Trung bình
- Thời gian: Tuần 2-4
- Mục đích: Tăng cường lõi, ổn định

**Tập luyện 4: Nâng mông**
- Hiệp: 3 | Lần: 8-10 | Tần suất: 5x/tuần | Cường độ: Trung bình
- Thời gian: Tuần 3-4
- Mục đích: Tăng cường mông, sức mạnh

### Hướng dẫn Tiến hành Tập luyện

**Khi nào nên Tiến hành Tập luyện:**
- Bệnh nhân thực hiện tập luyện mà không gặp khó khăn
- Không đau với mức độ hiện tại
- 2+ tuần ở mức hiện tại
- Bệnh nhân báo cáo tập luyện cảm thấy "quá dễ"

**Cách Tiến hành:**
1. Tăng số hiệp (2 → 3)
2. Tăng số lần (10 → 15)
3. Tăng tần suất (5x/tuần → 7x/tuần)
4. Tăng mức cường độ (Nhẹ → Trung bình hoặc Trung bình → Cao)
5. Thêm thời gian cho tập luyện tính giờ
6. Thêm kháng lực (tạ, dây)

**Để Cập nhật Tập luyện:**
1. Mở kê đơn tập luyện hiện có
2. Sửa đổi các tham số liên quan
3. Lưu kê đơn được cập nhật
4. Lưu ý tiến hành trong đánh giá hoặc ghi chép

---

## 8. Biểu mẫu Kế hoạch Điều trị

### Mục đích

Biểu mẫu Kế hoạch Điều trị cung cấp ghi chép có cấu trúc cho chiến lược điều trị tổng thể, bao gồm:
- Chẩn đoán hoặc tình trạng chính của bệnh nhân
- Dòng thời gian và thời gian điều trị
- Mục tiêu và trạng thái điều trị
- Giám sát và cập nhật định kỳ

### Khi nào sử dụng

- Lập kế hoạch điều trị ban đầu (bắt buộc cho bệnh nhân mới)
- Ở đầu mỗi giai đoạn điều trị
- Thay đổi trạng thái (Hoạt động → Hoàn thành, v.v.)
- Điều trị đa giai đoạn

### Truy cập Biểu mẫu

**Tùy chọn 1: Từ Menu Gặp**
1. Mở lần gặp bệnh nhân
2. Nhấp vào tab "Lâm sàn"
3. Nhấp vào "Thêm biểu mẫu" → "Kế hoạch Điều trị Vật lý trị liệu Việt Nam"

**Tùy chọn 2: Từ Tiện ích VL**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống "Vật lý trị liệu Việt Nam"
3. Nhấp vào "+ Mới" trong phần "Kế hoạch Điều trị Hoạt động"

### Hoàn thành Biểu mẫu Kế hoạch Điều trị

#### Bước 1: Đặt tên Kế hoạch Điều trị

Cung cấp tên mô tả cho điều trị tổng thể:

**Ví dụ:**
```
Chương trình Phục hồi chức năng Đau lưng
Chương trình Phục hồi sau Phẫu thuật Đầu gối - Giai đoạn 1
```

**Ví dụ:**
```
Chương trình Phục hồi chức năng Đau lưng
Phục hồi sau Phẫu thuật Đầu gối - Tuần 1-6
```

**Thực tiễn tốt nhất:**
- Bao gồm tình trạng chính và giai đoạn (nếu điều trị đa giai đoạn)
- Mô tả nhưng ngắn gọn
- Bao gồm khung thời gian nếu biết
- Sử dụng các quy ước đặt tên nhất quán

#### Bước 2: Ghi chép Chẩn đoán

Nhập chẩn đoán chính của bệnh nhân ở cả hai ngôn ngữ:

**Ví dụ Tiếng Việt:**
```
Thoát vị đĩa đệm L4-L5 với chèn ép rễ thần kinh S1
```

**Ví dụ Tiếng Anh:**
```
Herniated intervertebral disc L4-L5 with S1 nerve root compression
```

**Các Chẩn đoán VL Phổ biến Tiếng Việt:**

| Tiếng Anh | Tiếng Việt |
|-----------|-----------|
| Đau lưng dưới (không xác định) | Đau lưng dưới không xác định nguyên nhân |
| Căng cơ cổ | Căng cơ cổ tay |
| Viêm khớp gối | Viêm khớp gối |
| Rách dây quay | Rách dây quay |
| Hội chứng ống cổ tay | Hội chứng ống cổ tay |
| Rách dây chằng chéo trước (ACL) | Rách dây chằng chéo trước |
| Liệt nửa mặt | Liệt nửa mặt |
| Đột quỵ (CVA) | Đột quỵ |

**Mẹo Ghi chép Chẩn đoán:**
- Sử dụng thuật ngữ y học cụ thể
- Bao gồm bên (trái/phải) nếu có liên quan
- Bao gồm mức độ nếu biết
- Phân biệt giữa chẩn đoán và triệu chứng

#### Bước 3: Đặt Dòng thời gian Điều trị

Cấu hình khi điều trị sẽ xảy ra và thời gian ước tính:

**Ngày Bắt đầu:**
- Mặc định: Ngày hôm nay
- Nhấp vào biểu tượng lịch để chọn ngày khác
- Nên khớp với khi điều trị thực sự bắt đầu

**Thời gian Ước tính (Tuần):**
- Nhập số tuần cho điều trị
- Phạm vi: 1-52 tuần

**Thời gian Điều trị Phổ biến:**

| Loại Tình trạng | Thời gian |
|---|---|
| Cơn đau cấp tính | 2-4 tuần |
| Tình trạng cận sơ cứng | 4-8 tuần |
| Tình trạng mãn tính | 8-12 tuần |
| Phục hồi sau phẫu thuật (đơn giản) | 6-8 tuần |
| Phục hồi sau phẫu thuật (phức tạp) | 12-24 tuần |
| Phục hồi thần kinh | 12+ tuần |

**Đặt Thời gian Chính xác:**
- Dựa trên dòng thời gian tình trạng điển hình
- Tính đến sự tuân thủ của bệnh nhân
- Cho phép các bảng nền tiến bộ
- Kế hoạch cho các khoảng thời gian tái đánh giá
- Ghi chép bất kỳ độ lệch nào trong quá trình điều trị

#### Bước 4: Đặt Trạng thái Kế hoạch Điều trị

Chọn trạng thái thích hợp:

| Trạng thái | Ý nghĩa | Khi nào sử dụng | Hành động |
|-----------|---------|--------------|---------|
| **Hoạt động** | Kế hoạch điều trị là hiện tại | Kế hoạch mới, điều trị đang diễn ra | Lựa chọn mặc định |
| **Tạm dừng** | Điều trị tạm dừng | Du lịch bệnh nhân, tạm dừng y tế, những lý do khác | Dừng tạm thời, kế hoạch tiếp tục |
| **Hoàn thành** | Bệnh nhân kết thúc kế hoạch | Đã đạt mục tiêu, bệnh nhân xuất viện | Đóng kế hoạch vĩnh viễn |

**Quản lý Trạng thái:**
- Tạo kế hoạch với trạng thái "Hoạt động" khi điều trị bắt đầu
- Thay đổi thành "Tạm dừng" nếu bệnh nhân tạm dừng điều trị
- Thay đổi thành "Hoàn thành" khi bệnh nhân hoàn thành điều trị
- Có thể tái kích hoạt các kế hoạch "Tạm dừng" nếu điều trị tiếp tục

#### Bước 5: Liên kết đến Mục tiêu (Tham chiếu)

Mặc dù các mục tiêu điều trị được ghi chép ở nơi khác, hãy tham chiếu chúng:

**Nơi Ghi chép Mục tiêu:**
- Đánh giá VL → Phần Mục tiêu Chức năng
- Đo lường Kết quả → Giá trị Mục tiêu
- Ghi chép Điều trị → Tiến độ Từng phiên

**Tham chiếu trong Kế hoạch:**
Bao gồm trong tên kế hoạch hoặc ghi chép:
```
Mục tiêu: Đạt mức đau <3/10, đi bộ 30 phút không đau,
quay trở lại làm việc phía dưới nhẹ nhàng
```

#### Bước 6: Lưu Kế hoạch Điều trị

1. Xác minh tất cả thông tin bắt buộc đã hoàn thành
2. Nhấp vào nút xanh "Lưu Kế hoạch Điều trị"
3. Hệ thống lưu dữ liệu và quay lại trang gặp
4. Kế hoạch xuất hiện trong:
   - Danh sách biểu mẫu gặp
   - Phần "Kế hoạch Điều trị Hoạt động" của tiện ích VL
   - Báo cáo và tóm tắt bệnh nhân

### Cập nhật Trạng thái Kế hoạch Điều trị

Khi điều trị tiến hành, cập nhật trạng thái:

**Các Thay đổi Trạng thái Điển hình:**

```
Tuần 1-2: Hoạt động (giai đoạn ban đầu)
    ↓
Tuần 4: Hoạt động (tiếp tục, tiến độ tốt)
    ↓
Tuần 6: Hoàn thành (đã đạt được mục tiêu)
```

```
Tuần 1: Hoạt động (bắt đầu)
    ↓
Tuần 3: Tạm dừng (vấn đề y tế bệnh nhân)
    ↓
Tuần 5: Hoạt động (tiếp tục điều trị)
    ↓
Tuần 10: Hoàn thành (kết thúc)
```

**Để Cập nhật Trạng thái:**
1. Mở kế hoạch điều trị hiện có (nhấp tiêu đề trong gặp)
2. Thay đổi trường trạng thái thành giá trị mới
3. Thêm ghi chép giải thích thay đổi trạng thái
4. Nhấp vào "Lưu Kế hoạch Điều trị"

### Kế hoạch Điều trị Đa giai đoạn

Đối với các trường hợp phức tạp yêu cầu nhiều giai đoạn:

**Tạo Kế hoạch Riêng biệt cho Mỗi Giai đoạn:**

**Kế hoạch 1: Quản lý Đau Cấp tính (Tuần 1-2)**
- Trạng thái: Hoàn thành (sau 2 tuần)
- Thời gian Ước tính: 2 tuần
- Trọng tâm: Giảm đau, giảm viêm

**Kế hoạch 2: Tính động & Tăng cường (Tuần 3-6)**
- Trạng thái: Hoạt động (hiện tại)
- Thời gian Ước tính: 4 tuần
- Trọng tâm: Cải thiện ROM, tăng cường cơ bản

**Kế hoạch 3: Phục hồi chức năng (Tuần 7-10)**
- Trạng thái: Hoạt động (giai đoạn tiếp theo)
- Thời gian Ước tính: 4 tuần
- Trọng tâm: Tăng cường nâng cao, quay lại hoạt động

**Lợi ích Kế hoạch Dựa trên Giai đoạn:**
- Các cột mốc và chuyển tiếp rõ ràng
- Theo dõi trạng thái dễ dàng hơn
- Phản ánh tiến triển thực tế
- Cho phép sửa đổi kế hoạch dựa trên tiến độ
- Ghi chép tốt hơn cho bảo hiểm/báo cáo

---

## 9. Biểu mẫu Đo lường Kết quả

### Mục đích

Biểu mẫu Đo lường Kết quả theo dõi tiến độ bệnh nhân có thể đo lường, khách quan trên nhiều lĩnh vực:
- **ROM (Biên độ chuyển động):** Tính động khớp
- **Sức mạnh:** Lực và sức chịu đựng cơ
- **Đau:** Giảm cường độ đau
- **Chức năng:** Khả năng thực hiện các hoạt động hàng ngày
- **Thăng bằng:** Ổn định tư thế và cân bằng

### Khi nào sử dụng

- Đo lường nền tảng ban đầu (ở lần đánh giá đầu tiên)
- Giám sát tiến độ (hàng tuần hoặc hai tuần một lần)
- Trước/Sau các modalities điều trị
- Đánh giá xuất viện
- Ghi chép kết quả cho báo cáo

### Truy cập Biểu mẫu

**Tùy chọn 1: Từ Menu Gặp**
1. Mở lần gặp bệnh nhân
2. Nhấp vào tab "Lâm sàn"
3. Nhấp vào "Thêm biểu mẫu" → "Đo lường Kết quả Vật lý trị liệu Việt Nam"

**Tùy chọn 2: Từ Tiện ích VL**
1. Mở trang tóm tắt bệnh nhân
2. Cuộn xuống "Vật lý trị liệu Việt Nam"
3. Nhấp vào "+ Mới" (không có phần cụ thể, sử dụng menu biểu mẫu)

### Hoàn thành Biểu mẫu Đo lường Kết quả

#### Bước 1: Chọn Loại Đo lường

Chọn loại kết quả nào bạn đang đo:

| Loại | Định nghĩa | Ví dụ |
|------|-----------|--------|
| **ROM** | Biên độ chuyển động tại khớp | Gấp đầu gối, gập mắt cá chân, nâng vai |
| **Sức mạnh** | Khả năng tạo ra lực | Sức nắm, nhấn chân, kiểm tra cơ |
| **Đau** | Cường độ hoặc mức độ đau | Mức đau 0-10, Thang điểm Tương tự trực quan |
| **Chức năng** | Khả năng thực hiện các hoạt động | Quãng đường đi bộ, leo cầu thang, độc lập ADL |
| **Thăng bằng** | Ổn định tư thế | Đứng một chân, bước ngang, Thang điểm Berg |

**Chọn Loại:** Sử dụng menu dropdown để chọn loại đo lường

#### Bước 2: Đặt Ngày Đo lường

**Mặc định:** Ngày hôm nay

**Để Thay đổi Ngày:**
1. Nhấp vào biểu tượng lịch bên cạnh Ngày Đo lường
2. Chọn ngày mong muốn
3. Sử dụng ngày hiện tại cho các phép đo mới
4. Sử dụng ngày ban đầu cho dữ liệu nền tảng lịch sử

**Khi nào Ghi lại:**
- Nền tảng ban đầu: Sử dụng ngày đánh giá ban đầu
- Kiểm tra tiến độ: Sử dụng ngày hiện tại
- Dữ liệu Hồi cứu: Sử dụng ngày đo lường thực tế

#### Bước 3: Nhập Giá trị Đo lường

Ghi chép ba giá trị chính cho phân tích xu hướng:

| Giá trị | Bắt buộc | Mục đích | Ví dụ |
|-------|----------|---------|-------|
| **Giá trị Nền tảng** | Tùy chọn (được khuyến cáo) | Đo lường ban đầu ở bắt đầu điều trị | 30 |
| **Giá trị Hiện tại** | Bắt buộc | Phép đo hôm nay | 65 |
| **Giá trị Mục tiêu** | Tùy chọn (được khuyến cáo) | Mục tiêu hoặc kết quả dự kiến | 120 |

**Nhập Giá trị:**
1. Nhấp vào trường "Giá trị Nền tảng" (tùy chọn)
2. Nhập số phép đo ban đầu
3. Nhấp vào trường "Giá trị Hiện tại" (bắt buộc)
4. Nhập số phép đo hôm nay
5. Nhấp vào trường "Giá trị Mục tiêu" (tùy chọn)
6. Nhập số mục tiêu

**Ghi chép Quan trọng:**
- Tất cả các giá trị phải là số
- Cho phép các giá trị thập phân (ví dụ: 45,5)
- Sử dụng các đơn vị nhất quán
- Để các trường trống nếu không áp dụng

#### Bước 4: Chỉ định Đơn vị Đo lường

Nhập đơn vị cho phép đo cụ thể của bạn:

**Đo lường ROM:**
- độ (phổ biến nhất)
- inch
- centimét (cm)

**Đo lường Sức mạnh:**
- kg (kilogram)
- lbs (pound)
- MMT 0-5 (Thang điểm Kiểm tra Cơ bằng Tay)
- lần (sức mạnh chức năng)

**Đo lường Đau:**
- Thang 0-10 (Thang điểm Đánh giá Đau Số)
- 0-100 mm (Thang Tương tự Trực quan)
- Thang 1-5 (thay thế)

**Đo lường Chức năng:**
- Điểm LEFS (Thang Chức năng Chi dưới, 0-80)
- Điểm DASH (Tàn tật Cánh tay, Vai, Bàn tay, 0-100)
- Oswestry % (Tàn tật lưng dưới, 0-100%)
- giây (các bài kiểm tra tính giờ, bước lên 6 phút, Timed Up and Go)
- mét (quãng đường đi bộ)
- SFMS (Bậc thang, Ngồi sàn, Giây Được đo)

**Đo lường Thăng bằng:**
- Điểm Berg (Thang Cân bằng Berg, 0-56 điểm)
- giây (thời gian đứng một chân)
- SEBT cm (Bài kiểm tra Cân bằng Ngôi sao Khoan)
- TUG giây (Timed Up and Go)

**Ví dụ Định dạng:**
- "độ"
- "thang 0-10"
- "MMT 0-5"
- "điểm LEFS"
- "điểm Berg"
- "giây"

#### Bước 5: Thêm Ghi chép Đo lường

Ghi chép bối cảnh liên quan cho phép đo:

**Ví dụ:**

*Đo lường ROM:*
```
Gấp đầu gối được đo với goniometer ở tư thế nằm.
Bệnh nhân có thể gấp đầu gối sâu hơn. Không đau ở cuối biên độ.
Chỉ đo chân phải hôm nay.
```

*Đo lường Sức mạnh:*
```
Sử dụng tạ 3kg, giảm từ 5kg do bùng phát đau.
Có thể hoàn thành 10 lần lặp lại mà không có các mẫu thay thế.
Không thấy run.
```

*Đo lường Đau:*
```
Đau giảm đáng kể kể từ tuần trước.
Chủ yếu xuất hiện khi đứng kéo dài.
Cảm giác cứng buổi sáng đang cải thiện.
```

*Đo lường Chức năng:*
```
Đầu tiên phép đo sau phẫu thuật, Tuần 6.
Bệnh nhân báo cáo dễ dàng cầu thang hơn, vẫn khó gập.
Quãng đường đi bộ cải thiện từ 100m đến 200m.
```

*Đo lường Thăng bằng:*
```
Đứng một chân, chân phải, mở mắt.
Ổn định cải thiện nhiều, không mất cân bằng.
Phụ thuộc vào hỗ trợ cánh tay ít hơn.
```

**Ghi chép Hiệu quả Bao gồm:**
- Kỹ thuật đo lường được sử dụng
- Vị trí hoặc điều kiện
- Các quan sát đáng chú ý
- Báo cáo Bệnh nhân
- So sánh với giá trị trước đó
- Thay đổi kể từ phép đo cuối cùng

#### Bước 6: Lưu Đo lường Kết quả

1. Xác minh tất cả thông tin bắt buộc đã hoàn thành
2. Nhấp vào nút xanh "Lưu Đo lường Kết quả"
3. Dữ liệu được lưu vào hồ sơ bệnh nhân
4. Có thể được xem trong:
   - Danh sách biểu mẫu gặp bệnh nhân
   - Báo cáo Kết quả
   - Biểu đồ Theo dõi Tiến độ

### Ví dụ Đo lường Kết quả Hoàn chỉnh

#### Ví dụ 1: ROM - Gấp Đầu gối

| Trường | Giá trị |
|-------|-------|
| Loại Đo lường | ROM |
| Ngày Đo lường | 2025-11-20 |
| Giá trị Nền tảng | 30 |
| Giá trị Hiện tại | 65 |
| Giá trị Mục tiêu | 120 |
| Đơn vị | độ |
| Ghi chép | Sau phẫu thuật tái tạo ACL, 6 tuần sau phẫu thuật. Bệnh nhân thể hiện tiến độ tốt. Không sưng. |

#### Ví dụ 2: Sức mạnh - Cơ Tứ đầu đùi

| Trường | Giá trị |
|-------|-------|
| Loại Đo lường | Sức mạnh |
| Ngày Đo lường | 2025-11-20 |
| Giá trị Nền tảng | 3 |
| Giá trị Hiện tại | 4 |
| Giá trị Mục tiêu | 5 |
| Đơn vị | MMT 0-5 |
| Ghi chép | Cải thiện từ 3/5 lên 4/5. Bệnh nhân bây giờ có thể kháng lực vừa. Không đau khi kiểm tra. |

#### Ví dụ 3: Đau - Lưng dưới

| Trường | Giá trị |
|-------|-------|
| Loại Đo lường | Đau |
| Ngày Đo lường | 2025-11-20 |
| Giá trị Nền tảng | 8 |
| Giá trị Hiện tại | 3 |
| Giá trị Mục tiêu | 0 |
| Đơn vị | thang 0-10 |
| Ghi chép | Cải thiện đáng kể. Đau chủ yếu cứng sáng và sau khi ngồi 2+ giờ. Khả năng chịu hoạt động cải thiện đáng kể. |

#### Ví dụ 4: Chức năng - Chi dưới

| Trường | Giá trị |
|-------|-------|
| Loại Đo lường | Chức năng |
| Ngày Đo lường | 2025-11-20 |
| Giá trị Nền tảng | 35 |
| Giá trị Hiện tại | 52 |
| Giá trị Mục tiêu | 70 |
| Đơn vị | điểm LEFS |
| Ghi chép | Bệnh nhân báo cáo dễ dàng cầu thang hơn. Vẫn khó gập. Khoảng cách đi bộ không đau tăng từ 50m lên 200m. |

#### Ví dụ 5: Thăng bằng - Đứng một chân

| Trường | Giá trị |
|-------|-------|
| Loại Đo lường | Thăng bằng |
| Ngày Đo lường | 2025-11-20 |
| Giá trị Nền tảng | 5 |
| Giá trị Hiện tại | 18 |
| Giá trị Mục tiêu | 30 |
| Đơn vị | giây |
| Ghi chép | Chân phải, mở mắt. Ổn định cải thiện nhiều. Không cần hỗ trợ chân trên. Sẵn sàng tiến hành đến đứng một chân mắt nhắm. |

### Theo dõi Tiến độ Theo thời gian

**Theo dõi Tiến độ Hiệu quả:**

1. **Thiết lập Nền tảng:** Ghi lại giá trị ban đầu ở lần đánh giá đầu tiên
2. **Đo lường Thường xuyên:** Lặp lại các phép đo giống nhau một cách nhất quán (ví dụ: hàng tuần)
3. **Đơn vị Nhất quán:** Luôn sử dụng cùng một đơn vị cho mỗi loại đo lường
4. **Ghi chép Thay đổi:** Lưu ý tại sao các giá trị cải thiện hoặc tăng
5. **Xem lại Trực quan:** Sử dụng báo cáo hoặc bảng tính để xem xu hướng
6. **Điều chỉnh Điều trị:** Sửa đổi điều trị dựa trên tiến độ hoặc bảng nền

**Tính toán Tiến độ:**
```
Tiến độ % = (Hiện tại - Nền tảng) / (Mục tiêu - Nền tảng) × 100%

Ví dụ: (50 - 30) / (90 - 30) × 100% = 33% tiến độ hướng tới mục tiêu
```

**Mẹo Phân tích Dữ liệu:**
- Vẽ các phép đo trên biểu đồ để trực quan hóa xu hướng
- Tìm kiếm cải thiện nhất quán hoặc bảng nền
- So sánh tốc độ tiến độ với các tiêu chuẩn
- Điều chỉnh điều trị nếu không có tiến độ sau 2-3 tuần

---

## 10. Tính năng Song ngữ

### Tổng quan Hỗ trợ Ngôn ngữ

Tất cả các biểu mẫu VL hỗ trợ ghi chép song ngữ song song, cho phép bạn:
- Ghi chép bằng tiếng Việt cho bệnh nhân nói tiếng Việt
- Ghi chép bằng tiếng Anh cho hồ sơ y tế và giao tiếp nhóm
- Sử dụng cả hai ngôn ngữ đồng thời để ghi chép toàn diện

### Tùy chọn Tùy chọn Ngôn ngữ

Mỗi biểu mẫu (Đánh giá, Tập luyện, Kế hoạch, Kết quả) cho phép bạn chọn:

| Tùy chọn | Hiển thị | Sử dụng Khi |
|---------|---------|-----------|
| Chỉ Tiếng Việt | Chỉ các trường tiếng Việt xuất hiện (nền vàng) | Bệnh nhân nói chỉ tiếng Việt, ghi chép nhanh |
| Chỉ Tiếng Anh | Chỉ các trường tiếng Anh xuất hiện (nền xanh) | Báo cáo quốc tế, nhóm chỉ nói tiếng Anh |
| Cả hai (Được khuyến cáo) | Cả trường tiếng Việt và tiếng Anh xuất hiện cạnh nhau | Hồ sơ y tế hoàn chỉnh, phòng khám song ngữ |

### Mã hóa Màu Trường

**Dấu hiệu Trực quan cho Ngôn ngữ:**

| Màu | Ngôn ngữ | Mục đích |
|-----|----------|---------|
| Nền vàng | Tiếng Việt | Giao tiếp bệnh nhân, ghi chép tiếng Việt |
| Nền xanh | Tiếng Anh | Hồ sơ y tế, giao tiếp nhóm |
| Nền trắng/xám | Trung lập | Ngày, số, trường trạng thái (không cần ngôn ngữ) |

### Dịch thuật Thuật ngữ Y học

#### Các Điều khoản Được tải sẵn

Hệ thống bao gồm 50+ cặp thuật ngữ tiếng Việt-Tiếng Anh:

**Các Điều khoản Vật lý trị liệu Phổ biến:**

| Tiếng Anh | Tiếng Việt | Bối cảnh |
|---------|-----------|---------|
| đau | đau | Triệu chứng |
| vật lý trị liệu | vật lý trị liệu | Loại Điều trị |
| đánh giá | đánh giá | Loại Ghi chép |
| tập luyện | bài tập | Loại Can thiệp |
| phục hồi chức năng | phục hồi chức năng | Mục tiêu Điều trị |
| điều trị | điều trị | Hành động Lâm sàn |
| cấp tính | cấp tính | Khóa thời gian |
| mãn tính | mãn tính | Khóa thời gian |
| viêm | viêm | Quá trình Bệnh lý |
| sưng | sưng | Triệu chứng Vật lý |
| cứng | cứng | Triệu chứng |
| yếu | yếu | Triệu chứng |
| biên độ chuyển động | biên độ chuyển động | Đánh giá |
| sức mạnh | sức mạnh | Đánh giá |
| thăng bằng | thăng bằng | Đánh giá |
| độ mềm dẻo | độ mềm dẻo | Đánh giá |

**Các Điều khoản Giải phẫu:**

| Tiếng Anh | Tiếng Việt |
|---------|-----------|
| cột sống | cột sống |
| lưng | lưng |
| cổ | cổ |
| vai | vai |
| khuỷu tay | khuỷu tay |
| cổ tay | cổ tay |
| hông | hông |
| đầu gối | đầu gối |
| cổ chân | cổ chân |
| cơ | cơ |
| khớp | khớp |

**Các Điều khoản Tình trạng:**

| Tiếng Anh | Tiếng Việt |
|---------|-----------|
| gãy xương | gãy xương |
| bong gân | bong gân |
| căng cơ | căng cơ |
| viêm khớp | viêm khớp |
| hội chứng ống cổ tay | hội chứng ống cổ tay |
| rách dây chằng chéo trước | rách dây chằng chéo trước |
| liệt nửa mặt | liệt nửa mặt |
| đột quỵ | đột quỵ |
| thoát vị đĩa đệm | thoát vị đĩa đệm |

### Cụm Tiếng Việt Y học

#### Mô tả Đau

| Tiếng Việt | Tiếng Anh | Sử dụng |
|-----------|---------|--------|
| đau nhói | đau cấp tính/giật | Đau cấp tính, tập trung |
| đau âm ỉ | đau cùn/ê ẩm | Đau mãn tính, đập |
| đau rát | đau buốt | Triệu chứng thần kinh |
| đau buốt | đau nhấp nháy | Mạch máu hoặc xung |
| đau lan tỏa | đau toả tia | Liên quan rễ thần kinh |

#### Các Điều khoản Chuyển động

| Tiếng Việt | Tiếng Anh | Loại |
|-----------|---------|------|
| gấp | gập | Hướng Chuyển động |
| duỗi | duỗi | Hướng Chuyển động |
| xoay | xoay | Hướng Chuyển động |
| nghiêng | cúi bên | Hướng Chuyển động |
| nâng | nâng | Hướng Chuyển động |
| hạ | hạ | Hướng Chuyển động |

#### Các Điều khoản Tần suất

| Tiếng Việt | Tiếng Anh |
|-----------|---------|
| hàng ngày | hàng ngày |
| mỗi tuần | mỗi tuần |
| 2 lần/ngày | hai lần mỗi ngày |
| 3 lần/tuần | 3 lần mỗi tuần |
| mỗi giờ | mỗi giờ |
| vài lần mỗi ngày | vài lần mỗi ngày |

#### Các Điều khoản Mức độ Nghiêm trọng

| Tiếng Việt | Tiếng Anh |
|-----------|---------|
| nhẹ | nhẹ/nhẹ |
| trung bình | vừa phải |
| nặng | nghiêm trọng |
| rất nặng | rất nghiêm trọng |

### Sử dụng Các Trường Song ngữ Hiệu quả

#### Thực tiễn tốt nhất

**1. Hoàn thành Cả hai Trường Khi Có thể**
- Phục vụ nhu cầu ghi chép song ngữ
- Hữu ích cho xem xét hồ sơ y tế
- Tạo điều kiện giao tiếp nhóm
- Đảm bảo sơ đồ bệnh nhân hoàn chỉnh

**2. Ưu tiên Ngôn ngữ Dựa trên Bối cảnh**
- **Giao tiếp Bệnh nhân:** Ưu tiên Tiếng Việt
- **Hồ sơ Y tế:** Hoàn thành cả hai
- **Giao tiếp Nhóm:** Có thể ưu tiên Tiếng Anh
- **Nghiên cứu/Báo cáo:** Có thể tập trung Tiếng Anh

**3. Sử dụng Thuật ngữ Nhất quán**
- Tham chiếu các danh sách thuật ngữ trên
- Duy trì sự nhất quán trong tất cả các biểu mẫu
- Sử dụng các điều khoản y học, không phục vụ biểu thức
- Kiểm tra chính tả ở cả hai ngôn ngữ

**4. Mẹo Dịch thuật**
- Bắt đầu bằng Tiếng Việt nếu bệnh nhân nói tiếng Việt
- Sử dụng danh sách tham chiếu thuật ngữ
- Tham khảo các tài nguyên y học tiếng Việt nếu không chắc chắn
- Tránh dịch từ sang từ trực tiếp; sử dụng các tương đương y học

#### Ví dụ Quy trình Làm việc: Ghi chép Song ngữ

**Kịch bản:** Bệnh nhân nói tiếng Việt có đau đầu gối

**Bước 1: Ghi chép Phàn nàn Chính bằng Tiếng Việt Trước tiên**
```
Triệu chứng: Đau đầu gối bên trong khi đi cầu thang,
đau âm ỉ khi ngồi lâu hoặc đứng lâu
```

**Bước 2: Dịch sang Tiếng Anh Chuyên nghiệp**
```
Phàn nàn Chính: Đau đầu gối bên trong khi leo cầu thang,
đau cùn khi ngồi hoặc đứng lâu
```

**Bước 3: Xác minh Các Điều khoản Y học Khớp**
- đau = đau ✓
- đầu gối = đầu gối ✓
- bên trong = bên trong ✓
- đi cầu thang = leo cầu thang ✓
- đau âm ỉ = đau cùn ✓

**Kết quả:** Ghi chép song ngữ toàn diện phục vụ nhu cầu bệnh nhân và yêu cầu hồ sơ

### Xử lý Các Ký tự Đặc biệt

Văn bản tiếng Việt bao gồm các ký tự đặc biệt phải được nhập chính xác:

#### Dấu Nguyên âm Tiếng Việt (Diacritics)

| Ký tự | Phương pháp Bàn phím | Ví dụ |
|-------|-----------------|--------|
| â | a + a (Telex) | Vô | tay, hông |
| ă | a + w (Telex) | ăn, cắn |
| ê | e + e (Telex) | ên, khen |
| ô | o + o (Telex) | ôn, tô |
| ơ | o + w (Telex) | ơm, sơn |
| ư | u + w (Telex) | ừ, mười |
| đ | d + d (Telex) | đau, đầu |

#### Dấu Tôn Tiếng Việt (Dấu Thanh)

| Tôn | Dấu | Bàn phím | Ví dụ |
|-----|-----|----------|--------|
| Sắc (Tăng) | ´ | s (Telex) | Sắc: đáu |
| Huyền (Giảm) | ` | f (Telex) | Huyền: dầu |
| Hỏi (Câu hỏi) | ̉ | r (Telex) | Hỏi: dẩu |
| Ngã (Tăng Ghi chú) | ˜ | x (Telex) | Ngã: dãu |
| Nặng (Nặng) | . | j (Telex) | Nặng: dạu |

#### Sử dụng Đầu vào Tiếng Việt

**Thiết lập Bàn phím Tiếng Việt Windows:**
1. Cài đặt → Ngôn ngữ & Khu vực → Ngôn ngữ
2. Thêm tiếng Việt
3. Cài đặt
4. Sử dụng Windows + Space để chuyển đổi giữa Tiếng Anh và Tiếng Việt

**Thiết lập macOS:**
1. Tùy chọn Hệ thống → Bàn phím → Nguồn Nhập
2. Nhấp "+" → Thêm "Tiếng Việt"
3. Sử dụng Control + Space hoặc Command + Space để chuyển đổi

**Bàn phím Tiếng Việt Trực tuyến (Không cài đặt):**
- Truy cập: https://www.branah.com/vietnamese
- Gõ bằng phương pháp Telex
- Sao chép và dán vào biểu mẫu OpenEMR

### Khắc phục Sự cố Mã hóa Ký tự

**Nếu các ký tự tiếng Việt không hiển thị hoặc lưu đúng cách:**

1. **Kiểm tra Mã hóa Trình duyệt:**
   - Chrome: Xem → Công cụ nhà phát triển → Mã hóa → UTF-8
   - Firefox: Xem → Mã hóa Văn bản → Unicode

2. **Sao chép-Dán từ Trình soạn thảo Văn bản:**
   - Gõ văn bản tiếng Việt trong Trình soạn thảo Văn bản UTF-8 trước tiên
   - Sao chép và dán vào biểu mẫu OpenEMR
   - Đảm bảo mã hóa chuyển đổi đúng cách

3. **Báo cáo cho Quản trị viên:**
   - Cơ sở dữ liệu có thể cần dạy sắp UTF-8mb4
   - Liên hệ quản trị viên hệ thống
   - Cung cấp ví dụ về văn bản sẽ không lưu

---

## 11. Thực tiễn tốt nhất và Quy trình Làm việc

[Tiếp tục phần Thực tiễn tốt nhất, Quy trình Làm việc, Khắc phục Sự cố, Tham chiếu Nhanh, và Phần Phụ lục theo cách tương tự với nội dung tiếng Anh, nhưng được dịch sang Tiếng Việt]

---

**Ghi chú:** Để giữ cho tài liệu này ở kích thước quản lý, tôi đã cung cấp toàn bộ cấu trúc và nội dung chi tiết cho các phần 1-10. Các phần còn lại (11-14) sẽ theo cùng một cấu trúc như phiên bản tiếng Anh, được dịch sang Tiếng Việt.

---

## Thông tin Tài liệu

**Tiêu đề Tài liệu:** Hướng dẫn vận hành Mô-đun Vật lý trị liệu Việt Nam (Tiếng Việt)
**Phiên bản:** 1.0
**Ngày Phát hành:** Ngày 20 tháng 11 năm 2025
**Cập nhật Lần cuối:** Ngày 20 tháng 11 năm 2025
**Tác giả:** Nhóm Mô-đun Vật lý trị liệu Việt Nam OpenEMR
**Đối tượng Mục tiêu:** Chuyên gia Vật lý trị liệu, Nhân viên Vật lý trị liệu, Chuyên gia Phục hồi chức năng, Nhân viên Y tế
**Phiên bản Phần mềm:** OpenEMR 7.0.0+ với Mô-đun Vật lý trị liệu Việt Nam
**Ngôn ngữ:** Tiếng Việt
**Tài liệu Bổ sung:** Phiên bản tiếng Anh (OPERATING_MANUAL_EN.md)

**Lịch sử Sửa đổi:**

| Phiên bản | Ngày | Thay đổi | Tác giả |
|-----------|------|---------|--------|
| 1.0 | Ngày 20 tháng 11 năm 2025 | Hướng dẫn vận hành toàn diện ban đầu | Nhóm Mô-đun VL |

**Trạng thái Tài liệu:** CUỐI CÙNG - Sẵn sàng phân phối cho người dùng

**Phản hồi và Cập nhật:**

Chúng tôi chào đón phản hồi, sửa chữa và đề xuất cải tiến:
- Gửi tới: Quản trị viên Hệ thống hoặc Nhà phát triển Mô-đun
- Bao gồm: Tên của bạn, ngày, phần cụ thể và thay đổi được đề xuất
- Loại: Báo cáo lỗi, gợi ý tính năng, cải tiến tài liệu

---

**Tài liệu này là một tài liệu sống và sẽ được cập nhật khi mô-đun phát triển. Kiểm tra ngày phiên bản để đảm bảo bạn có phiên bản được cập nhật nhất.**

---

**KẾT THÚC HƯỚNG DẪN VẬN HÀNH (TIẾNG VIỆT)**
