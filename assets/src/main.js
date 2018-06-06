layui.define(['jquery', 'ztree.edit', 'bootstrap', 'table', 'wulaui'], (exports) => {
	let $ = layui.$, wulaui = layui.wulaui, table = layui.table, main = {
		cmToolbar   : null,
		chBtns      : null,
		editable    : 0,
		cNode       : null,
		cModel      : null,
		editURL     : null,
		deleteURL   : null,
		restoreURL  : null,
		newUrl      : null,
		pubURL      : null,
		dataURL     : null,
		ccUrl       : null,
		pubChBtn    : null,
		delChBtn    : null,
		restoreChBtn: null,
		cntGrid     : null,
		gridCols    : null,
		sortf       : null,
		searchParams: {
			status: 11
		},
		init(editable, gcols) {
			let sitePage = $('#cms-site-page'), me = this;
			sitePage.removeClass('hidden');
			this.editable = editable;
			this.gridCols = gcols;
			this.sortf    = new wulaui.map();
			//初始化栏目树
			$('#channel-tree').on('ztree.init', function (e) {
				$.extend(true, e.tree, {
					settings: me._configTree()
				});
			}).wulatree('load');
			this.newUrl       = wulaui.app('cms/site/page/add/');
			this.editURL      = wulaui.app('cms/site/page/edit/');
			this.deleteURL    = wulaui.app('cms/site/page/delch/');
			this.restoreURL   = wulaui.app('cms/site/page/restorech/');
			this.pubURL       = wulaui.app('cms/site/page/publishch/');
			this.ccUrl        = wulaui.app('cms/site/cache/clear/');
			this.dataURL      = wulaui.app('cms/site/data');
			this.cmToolbar    = $('#cms-toolbar');
			this.pubChBtn     = $('#pub-ch');
			this.delChBtn     = $('#delete-ch');
			this.restoreChBtn = $('#restore-ch');
			this.chBtns       = $('#ch-btns');
			this.chBtns.find('li').not('.showx').hide();
			this.cmToolbar.find('.ps').not('.s11').hide();
			this.pubChBtn.on('ajax.success', function () {
				let treeObj = $.fn.zTree.getZTreeObj("channel-tree");
				if (me.cNode) {
					let n    = treeObj.getNodeByParam('id', me.cNode.id);
					n.status = 1;
					n.name   = $(n.name).text();
					treeObj.updateNode(n);
					me.showBtns(n);
				}
			});
			this.delChBtn.on('ajax.success', function () {
				me._reloadTree();
			});
			this.restoreChBtn.on('ajax.success', function () {
				me._reloadTree(1);
			});
			$('#ch-status a').on('click', function () {
				let st = $(this).data('status'), cls = $(this).find('i').attr('class'), txt = $(this).text().trim();
				$('#reload-ch').find('i').attr('class', cls);
				$('#ch-box').text('[' + txt + ']');
				me.cmToolbar.find('.ps').hide();
				me.cmToolbar.find('.s' + st).show();
				me.searchParams.status = st;
				me.reload(me.cNode, me.cModel);
			});
			$('body').on('click', '#view-content a', function () {
				me.cModel = $(this).data('modelData');
				me.reload(me.cNode, me.cModel);
			}).on('ajax.before', 'a[lay-event]', function () {
				let curpage = $(this).data('curpage'), act = curpage.event, data = curpage.data;
				if (act === 'edit' && data.status === 2) {
					wulaui.toast.warning('不能编辑回收站里的内容');
					return false;
				}
			}).on('click', '#m-name', function () {
				me.reload(me.cNode, me.cModel);
			});
			//还原操作
			$('#btn-restore').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						if (e.status === 2) ids.push(e.page_id);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/page/restore'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要从回收站还原所选内容吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要还原的内容');
				}
			});
			$('#btn-ccache').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						ids.push(e.page_id);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/cache/clear'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要清空所选内容的缓存吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要清空缓存的内容');
				}
			});
			//下线操作-放入草稿箱
			$('#btn-unpub').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						if (e.status === 1) ids.push(e.page_id);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/page/unpub'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要将所选内容下线吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要下线的内容');
				}
			});
			//下线操作-放入草稿箱
			$('#btn-pending').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						if (e.revStatus === 0)
							ids.push(e.page_id);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/page/pending'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要将所选内容提交审核吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要送审的内容');
				}
			});
			//发布
			$('#btn-publish').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						if (e.revStatus === 1) ids.push(e.page_id + ',' + e.ver);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/page/publish'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要发布所选内容吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要发布的内容');
				}
			});
			//驳回
			$('#btn-nopub').on('click', function () {
				let selected = table.checkStatus('grid'), ids = [];
				if (selected.data.length > 0) {
					$(selected.data).each((i, e) => {
						if (e.revStatus === 1) ids.push(e.page_id + ',' + e.ver);
					});
					if (ids.length > 0) {
						wulaui.ajax.confirm({
							url    : wulaui.app('cms/site/page/nopublish'),
							element: $(this),
							data   : {
								ids: ids.join(',')
							}
						}, '你真要驳回所选内容吗?', {loading: true});
					}
				} else {
					wulaui.toast.warning('请选择要驳回的内容');
				}
			});
			//搜索按键
			$('#searchq').on('submit', function () {
				me.searchParams.q = $(this).find('input').val();
				me.reload(me.cNode, me.cModel);
				return false;
			});
			//reload
			$('#content-grid').data('loaderObj', {
				reload() {
					me.cntGrid.reload();
				}
			});
			//初始化内容表格
			me.cntGrid = table.render({
				id          : 'grid',
				elem        : '#content-grid',
				cols        : [[]],
				skin        : 'line',
				height      : 'full-50',
				method      : 'post',
				cellMinWidth: 60,
				page        : true,
				limit       : 20,
				limits      : [10, 20, 30, 40, 50],
				text        : {
					none: '无数据啊'
				}
			});
			//排序
			table.on('sort(grid)', obj => {
				if (obj.type) {
					me.sortf.put(obj.field, obj.type === 'desc' ? 'd' : 'a')
				} else {
					me.sortf.remove(obj.field);
					let objx = me.sortf.element(0);
					if (objx) {
						obj.field = objx.key;
						obj.type  = objx.value === 'd' ? 'desc' : 'asc';
					}
				}
				let sort             = me.sortf.join(',');
				me.searchParams.sort = {
					name: sort.keys,
					dir : sort.values
				};
				me.cntGrid.reload({
					where   : me.searchParams,
					initSort: obj
				});
			});
			table.on('tool(grid)', (obj) => {
				let toolbar = $(obj.tr[obj.tr.length - 1]), btn = toolbar.find('a[lay-event="' + obj.event + '"]'),
					pd                                          = obj.data, ver = '';
				if (btn.length > 0) {
					let url = btn.data('ogurl');
					if (!url) {
						url = btn.attr('href');
						btn.data('ogurl', url);
					}
					url += pd.page_id;
					if (obj.event === 'del') {
						if (pd.status === 2) {
							url += '/1';
							btn.data('confirm', '你真的要将其永久删除吗?');
						} else {
							if (pd.revStatus < 3) {
								url += '/0/' + pd.ver;
								btn.data('confirm', '你真的要删除这个版本?');
							} else {
								btn.data('confirm', '你真的要将其放入回收站吗?');
							}
						}
					} else if (pd.revStatus < 3) {
						url += '/' + pd.ver;
						ver = ',rev:' + pd.ver
					}
					btn.data('curpage', obj);
					btn.attr('href', url);
					if (btn.data('tab') !== undefined && btn.data('title')) {
						btn.data('title', btn.data('title').replace('{page_id}', 'ID:' + pd.page_id + ver));
					}
				}
			});
		},
		//刷新表格
		reload(node, m) {
			if (node && node.models.length > 0) {
				let model = m || node.models[node.models.length - 1],
					cols  = this.gridCols[model.refid] ? this.gridCols[model.refid] : [];
				$('#m-name').html(model.name);
				//重新加载内容列表
				this.sortf.clear();
				this.searchParams.chid = node.id;
				this.searchParams.mid  = model.refid;
				this.searchParams.sort = {
					name: 'page_id',
					dir : 'd'
				};
				this.sortf.put('page_id', 'd');
				let opts = {
					url  : this.dataURL,
					where: this.searchParams,

					page    : {
						curr: 1 //重新从第 1 页开始
					},
					initSort: {
						field: 'page_id',
						type : 'desc'
					}
				};

				opts.cols = cols;
				this.cntGrid.reload(opts);
			} else {
				this.searchParams.chid = 0;
				this.searchParams.mid  = 0;
				this.sortf.clear();
				delete this.searchParams.sort;
				this.cntGrid.reload({
					url  : this.dataURL,
					where: this.searchParams,
					cols : [],
					page : false
				});
			}
		},
		//处理按键
		createNewBtns(models) {
			let nc  = $('#new-content'),
				vc  = $('#view-content'),
				vcp = vc.closest('div'),
				me  = this;
			$('#add-sub-btn')
				.attr('href', this.newUrl + '1/' + this.cNode.id)
				.attr('title', '添加『' + this.cNode.name + '』子栏目')
				.parent().show();
			$('#edit-ch')
				.attr('href', this.editURL + me.cNode.id)
				.attr('title', '修改栏目『' + me.cNode.name + '』')
				.parent().show();
			vcp.hide();
			nc.find('.cm').empty();
			vc.find('.cm').empty();

			if (models.length > 0) {
				$(models).each(function (i, e) {
					if (e.enabled) {
						nc.prepend('<li class="cm"><a href="' + me.newUrl + e.id + '/' + me.cNode.id + '" data-tab="&#xe649;" data-title="新增『' + e.name + '』"><i class="fa fa-plus-square-o text-success"></i> ' + e.name + '</a></li>');
					}
					let ma = $('<li class="cm"><a href="javascript:">' + e.name + '</a></li>');
					ma.find('a').data('modelData', e);
					vc.prepend(ma);
				});

				vcp.show();
			}
			//删除栏目
			this.delChBtn.attr('href', this.deleteURL + me.cNode.id);
			//还原栏目
			this.restoreChBtn.attr('href', this.restoreURL + me.cNode.id);
			//清空缓存
			$('#cc-ch').attr('href', this.ccUrl + me.cNode.id);
			$('#cc-ch-all').attr('href', this.ccUrl + me.cNode.id + '/1');
			//栏目发布
			this.pubChBtn.find('a').attr('href', this.pubURL + me.cNode.id + '/1');
		},
		moveCh(treeId, treeNodes, targetNode, moveType) {
			let cnode     = treeNodes[0],
				cid       = cnode.id,
				oldParent = cnode.fromNode,
				oid       = oldParent ? oldParent.id : 0,
				nid       = targetNode.id,
				tid       = targetNode.id,
				tree      = $.fn.zTree.getZTreeObj(treeId);
			if (moveType !== 'inner') {
				let nd = targetNode.getParentNode();
				nid    = nd ? nd.id : 0;
			}
			wulaui.ajax.get(wulaui.app('cms/site/channel/move/'), {
				cid : cid,
				oid : oid,
				nid : nid,
				tid : tid,
				type: moveType
			}).done(data => {
				if (data.code === 200) {
					cnode.channel = nid;
					tree.updateNode(cnode);
					tree.moveNode(targetNode, cnode, moveType);
				}
			});
			return false;
		},
		showBtns(node) {
			let me = this;
			me.cmToolbar.addClass('hidden');
			me.cmToolbar.find('.act').hide();
			me.chBtns.find('.act').hide();
			if (node) {
				$('#ch-name').html(node.name);
				let btns = '.btn' + node.status;
				me.chBtns.find(btns).show();
				if (node.models.length > 0) {
					me.cmToolbar.find(btns).show();
					me.cmToolbar.removeClass('hidden');
				}
			} else {
				me.cNode = null;
				$('#ch-name').html('我的网站');
				me.chBtns.find('.showx').show();
			}
			me.reload(node);
		},
		//刷新栏目树
		_reloadTree(force) {
			let treeObj = $.fn.zTree.getZTreeObj("channel-tree");
			if (this.cNode) {
				let node = this.cNode.getParentNode();
				if (force === 1) {
					//找到不在回收站的节点
					while (node && node.status === 2) {
						node = node.getParentNode();
					}
				} else if (force === 2) {
					node = null;
				}
				treeObj.reAsyncChildNodes(node, "refresh");
			} else {
				treeObj.reAsyncChildNodes(null, "refresh");
			}
		},
		_configTree() {
			let me = this;
			return {
				view    : {
					showLine     : !0,
					nameIsHTML   : true,
					showTitle    : false,
					selectedMulti: false
				},
				data    : {
					keep: {
						leaf  : false,
						parent: true
					}
				},
				edit    : {
					enable       : me.editable,
					showRemoveBtn: false,
					showRenameBtn: false
				},
				callback: {
					onClick(e, treeId, treeNode) {
						me.cNode = treeNode;
						me.createNewBtns(treeNode.models);
						me.showBtns(treeNode);
						if (me.cNode && me.cNode.id === treeNode.id) {
							return false;
						}
						return false;
					},
					onAsyncSuccess(event, treeId) {
						if (me.cNode) {
							let treeObj = $.fn.zTree.getZTreeObj(treeId);
							let n       = treeObj.getNodeByParam('id', me.cNode.id);
							me.showBtns(n);
						} else {
							me.showBtns();
						}
					},
					beforeDrop(treeId, treeNodes, targetNode, type) {
						let pn                = targetNode;
						treeNodes[0].fromNode = treeNodes[0].getParentNode();
						if (type === 'inner') {
							if (targetNode.status !== 1) {
								wulaui.toast.warning('无法将栏目移动到回收站里的栏目');
								return false;
							}
							if (treeNodes[0].status === 2) {
								wulaui.toast.warning('无法移动回收站里的栏目');
								return false;
							}
						} else {
							if (treeNodes[0].channel === targetNode.channel) {
								return me.moveCh(treeId, treeNodes, targetNode, type);
							}
							pn = targetNode.getParentNode();
						}
						if (pn) {
							if (confirm('你真的要将栏目『' + treeNodes[0].name + '』移动到『' + pn.name + '』吗?')) {
								return me.moveCh(treeId, treeNodes, targetNode, type);
							}
						} else if (confirm('你真的要将栏目『' + treeNodes[0].name + '』变为顶级栏目吗?')) {
							return me.moveCh(treeId, treeNodes, targetNode, type);
						}
						return false;
					}
				}
			};
		}
	};

	exports('cms.main', main);
});