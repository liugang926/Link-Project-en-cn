$(document).ready(function() {
    // 添加淡入效果
    $('.fade-in').each(function(i) {
        $(this).delay(i*100).animate({'opacity':'1'}, 500);
    });

    // 图标预览
    $('#icon').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('.icon-preview').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    // 表单验证
    $('#linkForm').submit(function(e) {
        var requiredFields = ['title_zh', 'title_en', 'url', 'category_id'];
        var isValid = true;

        requiredFields.forEach(function(field) {
            if ($('#' + field).val() === '') {
                isValid = false;
                $('#' + field).addClass('is-invalid');
            } else {
                $('#' + field).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert(LANG === 'zh' ? "请填写所有必填字段。" : "Please fill in all required fields.");
        }
    });
});