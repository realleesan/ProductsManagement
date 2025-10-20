B. Thực hành kiểm thử thủ công
I. Phát biểu bài toán
1.1. Mô tả bài toán
Trong bối cảnh kinh doanh trực tuyến ngày càng phát triển, việc quản lý dữ liệu sản phẩm, đơn hàng và kho hàng đóng vai trò quan trọng trong hoạt động vận hành của các cửa hàng. Để đảm bảo tính chính xác và thuận tiện trong quá trình theo dõi, cập nhật thông tin, cần có một hệ thống website giúp người quản trị thực hiện các nghiệp vụ quản lý một cách tập trung và hiệu quả.
Website được xây dựng với mục tiêu hỗ trợ người quản lý theo dõi, thêm mới, chỉnh sửa, tìm kiếm và kiểm tra dữ liệu liên quan đến sản phẩm, kho hàng và đơn hàng. Hệ thống chỉ tập trung vào giao diện và chức năng phía quản trị viên, nhằm phục vụ việc kiểm thử và đánh giá quy trình quản lý nội bộ.
Thông qua hệ thống này, các thao tác như cập nhật tồn kho, thay đổi trạng thái sản phẩm, xử lý đơn hàng hay kiểm tra lịch sử hoạt động đều được tự động hóa, giúp giảm sai sót thủ công và tăng tính đồng bộ dữ liệu giữa các chức năng. Toàn bộ website gồm ba nhóm chức năng chính:
1.1.1. Chức năng quản lý sản phẩm
	Chức năng quản lý sản phẩm cho phép nhân viên và quản lý thêm mới, sửa đổi, xóa, tìm kiếm và thay đổi trạng thái hiển thị sản phẩm. Hệ thống kiểm tra tính hợp lệ của mã sản phẩm, tên, giá, tồn kho, ngày sản xuất, hạn sử dụng, danh mục liên kết và hình ảnh. Khi số lượng tồn kho bằng 0 hoặc sản phẩm hết hạn, trạng thái tự động chuyển thành “Hết hàng” hoặc “Expired” để không hiển thị trong bán hàng.
Luồng hoạt động sơ bộ đảm bảo danh mục sản phẩm luôn nhất quán và chính xác, cho phép quản lý phản hồi nhanh với thay đổi tồn kho hoặc cập nhật thông tin sản phẩm. Người dùng có thể thao tác trực tiếp trên từng sản phẩm, đồng thời hệ thống duy trì dữ liệu hợp lệ, hỗ trợ chức năng bán hàng và quản lý đơn hàng, đồng bộ với các ràng buộc đã xác định.
Ràng buộc:
Mã sản phẩm duy nhất, định dạng “SPXX…X”, không trùng trong toàn hệ thống.
Tên sản phẩm: bắt buộc, 5–150 ký tự, không chứa ký tự đặc biệt khó hiểu.
Giá bán: số dương, ≥1.000 VNĐ và ≤1.000.000.000 VNĐ.
Số lượng tồn kho: số nguyên ≥0; nếu =0 → sản phẩm tự động chuyển trạng thái “Hết hàng”.
Ngày sản xuất và hạn sử dụng hợp lệ; hạn sử dụng luôn sau ngày sản xuất ≥30 ngày.
Sản phẩm ở trạng thái “Expired” không được phép hiển thị hoặc bán ở chức năng bán hàng.
Danh mục liên kết phải tồn tại và còn hiệu lực; không thêm sản phẩm vào danh mục “Disabled”.
Trạng thái sản phẩm chỉ nhận “Active”, “Disabled”, “Out of stock”, “Expired”.
Hình ảnh sản phẩm: chỉ chấp nhận .jpg, .jpeg, .png; dung lượng ≤5MB; tối đa 5 ảnh.
Mô tả: tối đa 500 ký tự, không bắt buộc, nếu có phải thể hiện công dụng rõ ràng.
1.1.2. Chức năng quản lý kho
Chức năng quản lý kho cho phép nhân viên và quản lý theo dõi, nhập, xuất và điều chỉnh lượng hàng tồn của từng sản phẩm, đảm bảo dữ liệu kho luôn chính xác và đồng bộ với danh mục sản phẩm. Hệ thống hỗ trợ ghi nhận các phiếu nhập, phiếu xuất và tự động cập nhật số lượng tồn kho trong cơ sở dữ liệu sản phẩm. Khi có biến động về số lượng, hệ thống đồng thời kiểm tra trạng thái của sản phẩm để đảm bảo hiển thị đúng (“Active”, “Out of stock”, “Expired”).
Luồng hoạt động sơ bộ đảm bảo mỗi lần nhập hoặc xuất hàng đều được ghi nhận đầy đủ thông tin: sản phẩm, số lượng, người thực hiện, thời gian, loại giao dịch và ghi chú. Khi số lượng tồn kho đạt 0, hệ thống tự động chuyển sản phẩm sang trạng thái “Hết hàng” và cảnh báo để người quản lý kiểm tra. Ngược lại, khi nhập thêm hàng mới, trạng thái sản phẩm có thể được cập nhật lại thành “Active” nếu đủ điều kiện hiển thị.
Hệ thống cũng cho phép xem báo cáo tổng hợp tồn kho, lịch sử nhập xuất và tình trạng hàng hóa. Nhờ đó, người quản lý dễ dàng theo dõi luồng hàng hóa, phát hiện chênh lệch hoặc sai lệch dữ liệu giữa kho và sản phẩm, đồng thời có thể điều chỉnh lại khi phát hiện lỗi trong quá trình vận hành.
Ràng buộc:
Mỗi phiếu nhập/xuất có mã duy nhất, định dạng “PXYYYYMMDDXXX” hoặc “PNYYYYMMDDXXX”, không trùng lặp.
Sản phẩm ghi trong phiếu phải tồn tại và ở trạng thái hợp lệ (“Active” hoặc “Out of stock”), không cho phép thao tác với sản phẩm “Expired” hoặc “Disabled”.
Số lượng nhập, xuất là số nguyên dương ≥1; khi xuất hàng, số lượng không vượt quá tồn kho hiện tại.
Thời gian ghi nhận phiếu phải hợp lệ, không được ở tương lai.
Mỗi phiếu nhập/xuất phải có người thực hiện và lý do nhập/xuất rõ ràng (ví dụ: nhập hàng mới, điều chỉnh tồn, hủy đơn…).
Khi cập nhật tồn kho, hệ thống tự động đồng bộ với bảng sản phẩm để đảm bảo dữ liệu thống nhất.
Không được phép xóa phiếu đã ghi nhận; chỉ cho phép cập nhật thông tin trong cùng ngày nếu có quyền hợp lệ.
Mọi thao tác nhập, xuất, chỉnh sửa đều được ghi lại trong lịch sử để phục vụ kiểm tra và đối chiếu.
1.1.3. Chức năng quản lý đơn hàng
Chức năng quản lý đơn hàng hỗ trợ nhân viên và quản lý theo dõi, xử lý các đơn đặt hàng từ lúc khách tạo đơn cho đến khi hoàn tất hoặc hủy. Người dùng có thể xem danh sách đơn, tìm kiếm theo khách hàng, trạng thái, và cập nhật trạng thái hợp lệ. Mỗi đơn phải gắn với ít nhất một sản phẩm và thông tin khách hợp lệ, tổng giá trị đơn > 0, và hệ thống lưu lại lịch sử thao tác để đảm bảo minh bạch và kiểm tra đối chiếu.
Luồng hoạt động sơ bộ cho phép nhân viên xử lý các tình huống như đổi trạng thái, hủy đơn khi cần và nhập lý do hợp lệ. Khi đơn ở trạng thái “Hoàn tất” hoặc “Đã hủy”, hệ thống ngăn chỉnh sửa để bảo toàn dữ liệu. Cơ chế kiểm tra thời gian trạng thái đảm bảo mọi cập nhật luôn hợp lệ, phản ánh chính xác quá trình bán hàng, hỗ trợ quản lý ra quyết định dựa trên dữ liệu thực tế.
Ràng buộc:
Mã đơn hàng duy nhất, định dạng “DHYYYYMMDDXXX”, không trùng trong toàn hệ thống.
Trạng thái đơn hàng chỉ nhận các giá trị: “Chờ xác nhận”, “Đang xử lý”, “Đang giao”, “Hoàn tất”, “Đã hủy”.
Mỗi đơn hàng phải gắn với ít nhất một sản phẩm và một khách hàng hợp lệ; không cho phép đơn rỗng hoặc không xác định người mua.
Tổng giá trị đơn hàng = tổng tiền sản phẩm trong đơn, phải > 0.
Khi đơn ở trạng thái “Hoàn tất” hoặc “Đã hủy”, không được phép chỉnh sửa thông tin hoặc danh sách sản phẩm.
Nếu trạng thái chuyển từ “Đang giao” sang “Đã hủy”, bắt buộc ghi rõ lý do hủy (tối đa 200 ký tự).
Thời gian cập nhật trạng thái luôn sau thời điểm đặt đơn, không được lùi về trước.
Lịch sử thao tác (ngày tạo, người xác nhận, người giao, người cập nhật cuối) được lưu để phục vụ kiểm tra.
1.2. Yêu cầu chức năng và yêu cầu phi chức năng
1.2.1. Yêu cầu chức năng
a. Chức năng quản lý sản phẩm
Thêm mới sản phẩm
Người dùng nhập đầy đủ thông tin sản phẩm: mã sản phẩm, tên, giá, số lượng tồn kho, ngày sản xuất, hạn sử dụng, danh mục và hình ảnh.
Hệ thống kiểm tra các ràng buộc: mã duy nhất, tên hợp lệ, giá trong giới hạn, tồn kho ≥0, hạn sử dụng hợp lệ, hình ảnh đúng định dạng và dung lượng.
Nếu tất cả hợp lệ, sản phẩm được thêm vào danh mục. Nếu tồn kho = 0 hoặc sản phẩm hết hạn, trạng thái tự động chuyển thành “Hết hàng” hoặc “Expired”.
Kết quả: sản phẩm mới có thể hiển thị trong quản lý sản phẩm, chuẩn bị cho bán hàng khi đủ điều kiện.
Cập nhật thông tin sản phẩm
Người dùng chọn sản phẩm cần chỉnh sửa và cập nhật thông tin: giá, tên, hình ảnh, tồn kho, danh mục, mô tả.
Hệ thống kiểm tra tính hợp lệ theo ràng buộc. Mọi thay đổi liên quan đến tồn kho hoặc trạng thái được đồng bộ với chức năng bán hàng và quản lý đơn hàng.
Mọi thao tác được ghi nhận trong lịch sử để kiểm tra và đối chiếu.
Kết quả: thông tin sản phẩm được cập nhật chính xác, danh mục và trạng thái luôn hợp lệ.
Xóa sản phẩm
Người quản lý chọn sản phẩm cần xóa.
Hệ thống kiểm tra ràng buộc: sản phẩm không được liên kết với đơn hàng đang xử lý.
Nếu hợp lệ, sản phẩm bị xóa khỏi danh mục, các sản phẩm khác và dữ liệu bán hàng không bị ảnh hưởng.
Kết quả: cơ sở dữ liệu giữ nguyên tính toàn vẹn, báo cáo và chức năng bán hàng không gián đoạn.
Quản lý danh sách và tìm kiếm sản phẩm
Người dùng có thể lọc và tìm kiếm theo trạng thái, danh mục, tên, tồn kho.
Hệ thống chỉ hiển thị các sản phẩm hợp lệ, dữ liệu đầy đủ và chính xác.
Kết quả: nhân viên nhanh chóng nắm bắt tình hình tồn kho, sản phẩm sắp hết hạn hoặc cần cập nhật, chuẩn bị cho bán hàng và quản lý đơn hàng.
Thay đổi trạng thái hiển thị sản phẩm
Người dùng chọn sản phẩm và thay đổi trạng thái: “Active”, “Disabled”, “Out of stock”, “Expired”.
Hệ thống kiểm tra tồn kho, hạn sử dụng và các ràng buộc liên quan trước khi áp dụng.
Mọi thay đổi được ghi nhận trong lịch sử thao tác, đảm bảo dữ liệu nhất quán giữa các chức năng.
Kết quả: danh mục sản phẩm luôn chính xác, chỉ sản phẩm hợp lệ xuất hiện trong bán hàng.
b. Chức năng quản lý kho
Xem danh sách và tìm kiếm phiếu kho
Người dùng có thể xem toàn bộ danh sách phiếu nhập và xuất kho, đồng thời lọc theo loại phiếu (nhập/xuất), sản phẩm, người thực hiện hoặc thời gian.
Hệ thống đảm bảo dữ liệu hiển thị chính xác, có phân trang và đồng bộ với danh mục sản phẩm.
Kết quả: người quản lý dễ dàng theo dõi tình hình nhập – xuất, đối chiếu biến động hàng hóa và phát hiện sai lệch nếu có.
Tạo phiếu nhập kho
Người dùng nhập thông tin phiếu gồm: mã phiếu, sản phẩm, số lượng nhập, ngày nhập, người thực hiện và ghi chú.
Hệ thống kiểm tra ràng buộc: sản phẩm hợp lệ, số lượng > 0, ngày nhập không vượt quá hiện tại.
Khi hợp lệ, hệ thống tự động cập nhật số lượng tồn kho của sản phẩm và ghi nhận phiếu nhập trong danh sách.
Kết quả: kho được cập nhật chính xác, đảm bảo dữ liệu tồn kho khớp với quản lý sản phẩm.
Tạo phiếu xuất kho
Người dùng chọn sản phẩm cần xuất, nhập số lượng, ngày xuất, người thực hiện và lý do xuất.
Hệ thống kiểm tra điều kiện: sản phẩm hợp lệ, số lượng xuất không vượt quá số lượng tồn, ngày xuất hợp lệ.
Khi xác nhận, hệ thống tự động trừ tồn kho và ghi nhận phiếu xuất tương ứng. Nếu số lượng tồn về 0, trạng thái sản phẩm chuyển sang “Hết hàng”.
Kết quả: dữ liệu kho và sản phẩm luôn đồng bộ, phản ánh chính xác số lượng thực tế.
Xem chi tiết và chỉnh sửa phiếu kho
Người dùng có thể mở từng phiếu để xem chi tiết sản phẩm, số lượng, người thực hiện, thời gian và ghi chú.
Hệ thống cho phép chỉnh sửa thông tin trong cùng ngày tạo nếu người dùng có quyền hợp lệ.
Mọi thay đổi đều được ghi nhận vào lịch sử thao tác để đảm bảo tính minh bạch và phục vụ đối chiếu.
Kết quả: dữ liệu kho được duy trì chính xác, truy vết rõ ràng và dễ kiểm tra.
Báo cáo và thống kê tồn kho
Hệ thống tự động tổng hợp số liệu nhập – xuất – tồn theo sản phẩm hoặc theo khoảng thời gian.
Cho phép người dùng lọc sản phẩm còn hàng, hết hàng hoặc tồn dưới mức tối thiểu.
Kết quả: hỗ trợ nhà quản lý ra quyết định nhập hàng, điều chỉnh bán hàng và phát hiện chênh lệch kịp thời.
c. Chức năng quản lý đơn hàng 
Xem danh sách và tìm kiếm đơn hàng
Nhân viên và quản lý có thể xem tất cả đơn hàng hoặc lọc theo trạng thái, khách hàng, thời gian.
Hệ thống chỉ hiển thị đơn hợp lệ với dữ liệu đầy đủ: sản phẩm, khách hàng, tổng giá trị.
Kết quả: người dùng nhanh chóng nắm bắt tình trạng đơn hàng, chuẩn bị cho xử lý tiếp theo.
Cập nhật trạng thái đơn hàng
Nhân viên thay đổi trạng thái đơn: “Chờ xác nhận”, “Đang xử lý”, “Đang giao”, “Hoàn tất”, “Đã hủy”.
Hệ thống kiểm tra tính hợp lệ: thời gian cập nhật sau thời điểm đặt hàng, trạng thái hợp lệ, và nhập lý do hủy nếu cần.
Kết quả: đơn hàng phản ánh đúng trạng thái thực tế, dữ liệu đồng bộ với bán hàng và quản lý sản phẩm.
Xem chi tiết và kiểm tra đơn hàng
Người dùng có thể xem thông tin chi tiết của từng đơn: sản phẩm, số lượng, khách hàng, tổng tiền, phương thức thanh toán.
Hệ thống đảm bảo dữ liệu chính xác và khớp với cơ sở dữ liệu sản phẩm và giỏ hàng.
Kết quả: cung cấp thông tin để xử lý đơn, kiểm tra, đối chiếu và hỗ trợ khách hàng.
Hủy đơn hoặc điều chỉnh đơn hàng
Nhân viên có thể hủy đơn hoặc điều chỉnh thông tin khi hợp lệ.
Hệ thống kiểm tra trạng thái hiện tại của đơn (không cho phép chỉnh sửa khi “Hoàn tất” hoặc “Đã hủy”), và yêu cầu nhập lý do hợp lệ nếu hủy.
Kết quả: dữ liệu đơn hàng chính xác, không làm mất thông tin bán hàng hay báo cáo.
Lưu lịch sử thao tác
Mọi thao tác trên đơn hàng đều được ghi nhận: ngày tạo, người xác nhận, người giao, người cập nhật cuối.
Hệ thống đảm bảo tính minh bạch và phục vụ kiểm tra, đối chiếu, báo cáo.
Kết quả: cơ sở dữ liệu đầy đủ lịch sử, hỗ trợ quản lý ra quyết định và kiểm tra nội bộ.
1.2.2. Yêu cầu phi chức năng
a. Hiệu năng và phản hồi
Hệ thống phải phản hồi các thao tác cơ bản của người dùng (thêm sản phẩm vào giỏ, tìm kiếm, xem chi tiết sản phẩm, thanh toán) trong thời gian ≤ 2 giây với dữ liệu vừa phải (khoảng vài trăm sản phẩm, vài chục đơn hàng).
Tìm kiếm và lọc sản phẩm phải trả về kết quả nhanh, tối đa 1–2 giây với các điều kiện phổ biến (tìm kiếm theo tên, danh mục, trạng thái, tồn kho).
Hệ thống xử lý đồng thời tối thiểu 10–20 người dùng cùng lúc mà không gây lỗi hoặc treo trang.
b. Bảo mật
Ngăn chặn SQL Injection bằng cách sử dụng truy vấn có tham số (prepared statements), ORM của PHP.
Kiểm tra và xác thực dữ liệu đầu vào: mã sản phẩm, tên, giá, số lượng tồn kho, thông tin khách hàng, tránh nhập dữ liệu không hợp lệ hoặc dữ liệu độc hại.
Các thông tin nhạy cảm (ví dụ phương thức thanh toán, thông tin khách hàng) phải được xử lý an toàn và không lưu trữ dưới dạng plain text nếu cần thiết (mật khẩu, thông tin đăng nhập).
Quản lý phiên người dùng và xác thực truy cập để chỉ người quản lý/nhân viên mới thao tác chức năng quản lý sản phẩm và đơn hàng.
c. Tính ổn định và độ tin cậy
Hệ thống không được bị treo khi thao tác đồng thời hoặc nhập dữ liệu sai.
Cơ sở dữ liệu được thiết kế đảm bảo ràng buộc dữ liệu: khóa chính, khóa ngoại, trạng thái sản phẩm và đơn hàng, đảm bảo tính toàn vẹn.
Các thao tác như thêm, sửa, xóa sản phẩm hoặc cập nhật đơn hàng phải có cơ chế rollback khi xảy ra lỗi để không làm mất dữ liệu.
d. Khả năng mở rộng cơ bản
Cơ sở dữ liệu và mã nguồn được thiết kế để dễ dàng bổ sung thêm sản phẩm, danh mục, khách hàng hoặc đơn hàng mới mà không phải viết lại cấu trúc chính.
Chức năng quản lý và bán hàng có thể mở rộng thêm tính năng như nhiều phương thức thanh toán hoặc nhiều danh mục sản phẩm trong tương lai mà không ảnh hưởng cấu trúc hiện tại.
e. Môi trường hoạt động
Máy chủ: XAMPP, hỗ trợ PHP 8.x và MySQL/MariaDB.
Trình duyệt: Chrome, Firefox, Edge.
Thiết bị người dùng: máy tính hoặc laptop kết nối Internet ổn định.
Cơ sở dữ liệu: MySQL lưu trữ sản phẩm, đơn hàng, khách hàng, đảm bảo kết nối ổn định và bảo toàn dữ liệu.
1.3. Bảng giá trị
1.4. Bảng cơ sở dữ liệu
1.5. Ca sử dụng
1.6. Cấu trúc kiểm thử 
1.7. Giá trị đầu vào, giá trị đầu ra với các chức năng kiểm thử
