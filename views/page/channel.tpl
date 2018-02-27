<section class="hbox stretch wulaui" id="core-account-workset">
    <aside class="aside aside-md b-r">
        <section class="vbox">
            <header class="header bg-light b-b">
                <button class="btn btn-icon btn-default btn-sm pull-right visible-xs m-r-xs" data-toggle="class:show"
                        data-target="#core-role-wrap">
                    <i class="fa fa-reorder"></i>
                </button>
                <p class="h4">栏目</p>
            </header>
            <section class="hidden-xs scrollable w-f m-t-xs" id="core-role-wrap">
                <div id="core-role-list"  data-loading="#core-role-list">
                    <div class="wulaui">

                        <div id="lazy-tree"  data-lazy data-ztree="{'cms/page/tree'|app}"></div>

                    </div>
                </div>
            </section>

        </section>
    </aside>

    <aside class="aside aside-md b-r">

        <section class="hbox stretch">
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
    </aside>

</section>

<script>
	layui.use(['jquery', 'layer','ztree.edit','ztree','wulaui'], ($, layer) => {

		$('#lazy-tree').on('ztree.init', function (e) {
		$.extend(true, e.tree, {
			settings: {
				edit    : {
					enable       : true,
					showRemoveBtn: true,
					renameTitle  : "编辑节点名称",
					drag:{ isMove:true}
				},
				view    : {
					addHoverDom: addHoverDom,
					removeHoverDom: removeHoverDom
				},
				check   : {
					enable: false
				},
				callback: {
					onRename: onRename,
					beforeDrag: beforeDrag,
					beforeDrop: beforeDrop,
					beforeEditName: beforeEditName,
					beforeRemove: beforeRemove,
					onRemove: onRemove
				}
			}
		});
	}).wulatree('load');
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
	{literal}
	function onRename(e, treeId, treeNode, isCancel) {
		$("#top").hide();
		$("#id").val(treeNode['id']);
		$("#opath").val(treeNode['url']);
		get_page_info(treeNode['id']);
		console.log(treeNode);
	}

	function beforeEditName(treeId, treeNodes) {
		return confirm("Confirm rename node '" + treeNodes.name + "' it?");
	}

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

	var newCount = 1;
	function addHoverDom(treeId, treeNode) {
		//console.log(treeNode);
		var sObj = $("#" + treeNode.tId + "_span");
		if (treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length>0) return;
		var addStr = "<span class='button add' id='addBtn_" + treeNode.tId
			+ "' title='add node' onfocus='this.blur();'></span>";

		sObj.after(addStr);
		var btn = $("#addBtn_"+treeNode.tId);
		if (btn) btn.bind("click", function(){
//			var zTree = $.fn.zTree.getZTreeObj("lazy-tree");
//			zTree.addNodes(treeNode, {id:(100 + newCount), pId:treeNode.id, name:"new node" + (newCount++)});
//			return false;
            $("#pid").val(treeNode.id);
            $("#id").val('');
            $("#pname").val(treeNode.name);
			$("#top").show();
            return false;
		});
	}
	{/literal}
	})
</script>