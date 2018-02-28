<section class="hbox stretch wulaui" id="core-account-workset">
    <aside class="aside aside-lg b-r">
        <section class="vbox">
            <header class="header bg-light b-b">
                <button class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#core-role-wrap">
                    <i class="fa fa-reorder"></i>
                </button>
                <p class="h4">内容分类</p>
            </header>
            <section class="hidden-xs scrollable w-f m-t-xs" id="core-role-wrap">
                <div id="core-role-list"  data-loading="#core-role-list">
                    <div class="wulaui">
                        <div id="lazy-tree"  data-lazy data-ztree="{'cms/page/tree'|app}"></div>
                    </div>
                </div>
            </section>
            <footer class="footer bg-light lter b-t">
                <a class="btn btn-success btn-sm pull-right edit-role" id="add_channel">
                    <i class="fa fa-plus"></i> 新增
                </a>
                {*<a class="btn btn-danger btn-sm  edit-role"  id="del_channel" style="margin-left: 20px;">*}
                    {*<i class="fa fa-trash"></i> 删除*}
                {*</a>*}
                <a class="btn btn-success btn-sm pull-left edit-role" id="edit_channel">
                    <i class="fa fa-edit"></i> 编辑
                </a>

            </footer>
        </section>
    </aside>
    <section>
        <section class="hbox stretch">
            <aside class="aside hide" id="channel-editor">
                <div class="vbox">
                    <header class="header bg-light b-b">

                    </header>
                    <section class="w-f">
                        <div class="container-fluid m-t-md">
                            <div class="row wulaui">
                                <div class="col-sm-9">
                                    <form id="core-admin-form" name="AdminForm"
                                          action="{'cms/page/save_channel'|app}" data-ajax data-ajax-done="reload:#core-account-workset"
                                          method="post" data-loading>
                                        <input type="hidden" value="" id="pid" name="pid">
                                        <input type="hidden" value="" id="id" name="id">
                                        <input type="hidden" value="" id="ppath" name="ppath">
                                        <input type="hidden" value="" id="opath" name="opath">
                                        <div class="form-group pull-in clearfix" id="top">
                                            <div class="col-xs-6">
                                                <label>上级栏目</label><br>
                                                <input id="pname" type="text" name="name" value="" placeholder="不填为顶级" class="form-control ">
                                            </div>
                                        </div>
                                        <div class="form-group pull-in clearfix">
                                            <div class="col-xs-6">
                                                <label>标题</label><br>
                                                <input id="title" type="text" name="title" value=""  class="form-control" required>
                                            </div>
                                            <div class="col-xs-6">
                                                <label>副标题</label><br>
                                                <input id="ftitle" type="text" name="ftitle" value="" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group pull-in clearfix">
                                            <div class="col-xs-6">
                                                <label>Path</label><br>
                                                <input id="path" type="text" name="path" value=""  class="form-control" required>
                                            </div>
                                            <div class="col-xs-6">
                                                <label>关键词</label><br>
                                                <input id="keyword" type="text" name="keyword" value="" class="form-control ">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label>描述</label><br>
                                            <textarea id="description" rows="3" name="description" class="form-control "></textarea>
                                        </div>
                                        <div class="form-group">
                                            <div class="col-md-offset-3 col-md-9 col-xs-12">
                                                <button type="submit" class="btn btn-primary">保存</button>
                                                <button type="reset" class="btn btn-default">重置</button>
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                        </div>
                    </section>
                    <footer class="footer bg-light lter b-t">

                    </footer>
                </div>
            </aside>
            <aside class="aside" id="page-list">
                <div class="vbox">
                    <header class="header bg-light b-b">
                        aaa
                    </header>
                    <section class="w-f">
                        我是列表 啦啦啦
                    </section>
                    <footer class="footer bg-light lter b-t">

                    </footer>
                </div>
            </aside>
        </section>
    </section>

</section>

<script>
	layui.use(['jquery', 'layer','ztree.edit','ztree','wulaui'], ($, layer) => {

		$('#lazy-tree').on('ztree.init', function (e) {
		$.extend(true, e.tree, {
			settings: {
				edit    : {
					enable       : true,
					showRemoveBtn:true,
					showRenameBtn:false,
					drag:{ isMove:true}
				},
				view    : {
					//addHoverDom: addHoverDom,
					removeHoverDom: removeHoverDom
				},
				check   : {
					enable: false
				},
				callback: {
					//onRename: onRename,
					beforeDrag: beforeDrag,
					beforeDrop: beforeDrop,
					//beforeEditName: beforeEditName,
					beforeRemove: beforeRemove,
					onRemove: onRemove,
                    beforeClick: beforeClick
				}
			}
		});

	}).wulatree('load');
	function beforeClick(treeId, treeNode, clickFlag) {
		console.log(treeNode);
		console.log(clickFlag);
		$('#page-list').removeClass('hide');
		$('#channel-editor').addClass('hide');

	}
	//新增
	function addChannel(e) {
		var zTree = $.fn.zTree.getZTreeObj("lazy-tree"), nodes = zTree.getSelectedNodes();
		console.log(nodes[0]);
		$('#page-list').addClass('hide');
		$('#channel-editor').removeClass('hide');
		if (nodes.length == 0) {
           //添加顶级栏目
			$("#top").hide();
		}else {
			//添加子类
			$("#pid").val(nodes[0].id);
			$("#id").val('');
			$("#pname").val(nodes[0].name);
			$("#top").show();
			//去除可能存在的不需要的参数
			$("#title").val('');
			$("#ftitle").val('');
			$("#path").val('');
			$("#keyword").val('');
			$("#description").val('');
		}
	}
	//编辑
	function editChannel(e) {
		var zTree = $.fn.zTree.getZTreeObj("lazy-tree"), nodes = zTree.getSelectedNodes();
		console.log(nodes[0]);
		if (nodes.length == 0) {
			layer.msg('请选择一个内容进行编辑');
		}else {
			//添加子类
			$('#page-list').addClass('hide');
			$('#channel-editor').removeClass('hide');
			$("#top").hide();
			$("#id").val(nodes[0]['id']);
			$("#opath").val(nodes[0]['url']);
			get_page_info(nodes[0]['id']);
		}
	}
	//删除
	function delChannel(e) {
		var zTree = $.fn.zTree.getZTreeObj("lazy-tree"),
			type  = e.data.type,
			nodes = zTree.getSelectedNodes();
		console.log(nodes[0]);
		if (nodes.length == 0) {
			layer.msg('请选择一个内容进行删除');
		}else {
			$.get("{'cms/page/del_page'|app}/"+nodes[0]['id'],function (data) {
				console.log(data);
				if(!data){
					layer.msg('删除失败');
				}
			});
		}
	}
	//获取指定node的信息
    function get_page_info(id) {
		$.get("{'cms/page/get_page'|app}/"+id,function (data) {
			console.log(data);
			$("#title").val(data.title);
			$("#ftitle").val(data.ftitte);
			$("#path").val(data.path);
			$("#keyword").val(data.keywords);
			$("#description").val(data.description);
		});
	}
	//移动
	function beforeDrop(treeId, treeNodes, targetNode, moveType) {
		$("#top").show();
		console.log(treeNodes);
		console.log(targetNode);
		get_page_info(treeNodes[0]['id']);
		$("#pid").val(targetNode.id);
		$("#ppath").val(targetNode.url);
		$("#id").val(treeNodes[0]['id']);
		$("#opath").val(treeNodes[0]['url']);
		$("#pname").val(targetNode.name);
		return targetNode ? targetNode.drop !== false : true;
	}
	//删除
	function beforeRemove(treeId, treeNode) {
		return confirm("确定删除该节点吗?");

	}
	function onRemove(e, treeId, treeNode) {
		console.log(['删除后',treeNode]);
		$.get("{'cms/page/del_page'|app}/"+treeNode['id'],function (data) {
			console.log(data);
             if(!data){
             	layer.msg('删除失败');
			 }
		});
	}

	//操作
	$("#add_channel").bind("click",'', addChannel);
	$("#edit_channel").bind("click",'', editChannel);
	$("#del_channel").bind("click",'', delChannel);



	function beforeDrag(treeId, treeNodes) {
		for (var i=0,l=treeNodes.length; i<l; i++) {
			if (treeNodes[i].drag === false) {
				return false;
			}
		}
		return true;
	}

	function removeHoverDom(treeId, treeNode) {
		$("#addBtn_"+treeNode.tId).unbind().remove();
	}


	})
</script>