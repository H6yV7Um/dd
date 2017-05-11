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
            <form method="POST" action="http://127.0.0.1:8800/travel/index/publish" accept-charset="UTF-8" enctype="multipart/form-data"><input name="_token" type="hidden" value="LFjnUdbySVcpnmNlAgJL2os30BCLQDNqYdbUutfp">
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
                    <label for="price">价格</label>
                    <input class="form-control" name="price" type="text" id="price">
                </div>
                <!--- Hotel Field --->
                <div class="form-group">
                    <label for="hotel">Hotel:</label>
                    <select class="form-control" id="hotel" name="hotelType"><option value="1">单人间</option><option value="2">双人间</option><option value="3">多人间</option></select>
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
                    <label for="startPlace">出发地点</label>
                    <input class="form-control" name="startPlace" type="text" id="startPlace">
                </div>
                <div class="form-group">
                    <label for="endPlace">目的地</label>
                    <input class="form-control" name="endPlace" type="text" id="endPlace">
                </div>
                <!--- GoVehicle Field --->
                <div class="form-group">
                    <label for="goVehicle">去时交通</label>
                    <select class="form-control" id="goVehicle" name="goVehicle"><option value="1">大巴</option><option value="2">火车</option><option value="3">飞机</option><option value="4">轮船</option></select>
                </div>
                <div class="form-group">
                    <label for="backVehicle">返回交通</label>
                    <select class="form-control" id="backVehicle" name="backVehicle"><option value="1">大巴</option><option value="2">火车</option><option value="3">飞机</option><option value="4">轮船</option></select>
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