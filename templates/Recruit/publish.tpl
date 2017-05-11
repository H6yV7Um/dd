<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1"> -->
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>点滴</title>
    <!-- 新 Bootstrap 核心 CSS 文件 -->
    <link rel="stylesheet" href="//cdn.bootcss.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="/css/bootstrap-chinese-region.css">
    <!-- jQuery文件。务必在bootstrap.min.js 之前引入 -->
    <script src="//cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
    <!-- 最新的 Bootstrap 核心 JavaScript 文件 -->
    <script src="//cdn.bootcss.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="/js/bootstrap-chinese-region.js"></script>
</head>
<body>

<div class="container">

    <div class="col-xs-12 col-sm-10 col-sm-offset-1">
        <div class="row">
            <form method="POST" action="http://127.0.0.1:8800/recruit/index/publish" accept-charset="UTF-8" enctype="multipart/form-data"><input name="_token" type="hidden" value="7gIee7GrDv8jFQrxjQi55fiqHAp5PuEQ3wJrAbid">
                <div class="form-group">
                    <label for="catId">请选择分类</label>
                    <select class="form-control" id="catId" name="catId">
                        <%foreach $tplData['catList'] as $catInfo%>
                            <option value="<%$catInfo['catId']%>"><%$catInfo['catName']%></option>
                        <%/foreach%>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">标题</label>
                    <input class="form-control" name="title" type="text" id="title">
                </div>
                <div class="form-group">
                    <label for="image">图片</label>
                    <input class="form-control" name="image" type="file" id="image">
                </div>
                <div class="form-group">
                    <label for="content">详细内容</label>
                    <textarea class="form-control" rows="5" cols="50" name="content" id="content"></textarea>
                </div>
                <div class="form-group">
                    <label for="demand">要求</label>
                    <textarea class="form-control" rows="5" cols="50" name="demand" id="demand"></textarea>
                </div>
                <div class="form-group">
                    <label for="extra">备注</label>
                    <textarea class="form-control" rows="5" cols="50" name="extra" id="extra"></textarea>
                </div>
                <div class="form-group">
                    <label for="contact">联系人</label>
                    <input class="form-control" name="contact" type="text" id="contact">
                </div>
                <div class="form-group">
                    <label for="phoneNum">手机</label>
                    <input class="form-control" name="phoneNum" type="text" id="phoneNum">
                </div>
                <div class="form-group">
                    <label for="salary">薪资</label>
                    <input class="form-control" name="salary" type="text" id="salary">
                </div>
                <div class="form-group">
                    <label for="salaryType">薪资类型</label>
                    <select class="form-control" id="salaryType" name="salaryType"><option value="0">面议</option><option value="1">小时</option><option value="2">日</option><option value="3">周</option><option value="4">月</option><option value="5">年</option></select>
                </div>
                <div class="form-group">
                    <label for="number">招聘人数</label>
                    <input class="form-control" name="number" type="text" id="number">
                </div>
                <div class="form-group">
                    <label for="startDate">开始日期</label>
                    <input class="form-control" name="startDate" type="date" value="2017-05-11" id="startDate">
                </div>
                <div class="form-group">
                    <label for="endDate">结束日期</label>
                    <input class="form-control" name="endDate" type="date" value="2017-05-11" id="endDate">
                </div>
                <div class="form-group">
                    <label for="startTime">开始时间</label>
                    <input class="form-control" name="startTime" type="time" value="03:49" id="startTime">
                </div>
                <div class="form-group">
                    <label for="endTime">结束时间</label>
                    <input class="form-control" name="endTime" type="time" value="03:49" id="endTime">
                </div>
                <div class="form-group">
                    <label for="address">地址</label>
                    <div class="bs-chinese-region flat dropdown" data-submit-type="id" data-min-level="1" data-max-level="3">
                        <input type="text" class="form-control" name="address" id="address" placeholder="选择你的地区" data-toggle="dropdown" readonly="" value="411302">
                        <div class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                            <div>
                                <ul class="nav nav-tabs" role="tablist">
                                    <li role="presentation" class="active"><a href="#province" data-next="city" role="tab" data-toggle="tab">省份</a></li>
                                    <li role="presentation"><a href="#city" data-next="district" role="tab" data-toggle="tab">城市</a></li>
                                    <li role="presentation"><a href="#district" data-next="street" role="tab" data-toggle="tab">县区</a></li>
                                </ul>
                                <div class="tab-content">
                                    <div role="tabpanel" class="tab-pane active" id="province">--</div>
                                    <div role="tabpanel" class="tab-pane" id="city">--</div>
                                    <div role="tabpanel" class="tab-pane" id="district">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="detailAddress">详细地址</label>
                    <input class="form-control" name="detailAddress" type="text" id="detailAddress">
                </div>
                <input type="hidden" name="token" value="GRAWRQEWcVBECVceQVdVElpWVXxXEwgRURIJAQ0HC0BOEA9ZAw1WYA9eAxBZAgRRBAQHAQIBCk5tPiFxamp4IlJtPHYkO2d0JgdQBFdVCVMADVYFUldQVwFXU1IDBV0AUAdUAAYFXgZX">
                <div class="form-group">
                    <input class="btn btn-primary form-control" type="submit" value="保存">
                </div>
            </form>
        </div>
    </div>
</div>

</body>
<script type="text/javascript">
    $.getJSON('/sql_area.json',function(data){

        for (var i = 0; i < data.length; i++) {
            var area = {id:data[i].id,name:data[i].cname,level:data[i].level,parentId:data[i].upid};
            data[i] = area;
        }

        $('.bs-chinese-region').chineseRegion('source',data);
    });
</script>
</html>