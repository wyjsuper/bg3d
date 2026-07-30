/* 北港3D 后台交互脚本（原生 JS，替代 admin-frame.tsx + content-editor.tsx） */
(function () {
  "use strict";
  var BG_BASE = window.BG_BASE || '';

  function esc(s) {
    return String(s == null ? "" : s)
      .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;").replace(/'/g, "&#39;");
  }

  /* ============ 移动端抽屉 ============ */
  function initDrawer() {
    var drawer = document.querySelector(".mobile-drawer");
    if (!drawer) return;
    var openBtn = document.querySelector("[data-drawer-open]");
    if (openBtn) openBtn.addEventListener("click", function () { drawer.classList.remove("hidden"); });
    document.querySelectorAll("[data-drawer-close]").forEach(function (el) {
      el.addEventListener("click", function () { drawer.classList.add("hidden"); });
    });
  }

  /* ============ 退出登录 ============ */
  function initLogout() {
    document.querySelectorAll("[data-logout]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        fetch(BG_BASE + "/api/logout.php", { method: "POST" })
          .catch(function () {})
          .then(function () { window.location.href = BG_BASE + "/admin/login.php"; });
      });
    });
  }

  /* ============ 修改密码弹窗 ============ */
  function initPwModal() {
    var modal = document.querySelector(".pw-modal");
    if (!modal) return;
    var formWrap = modal.querySelector(".pw-form-wrap");
    var successBox = modal.querySelector(".pw-success");
    var errEl = modal.querySelector(".pw-error");
    var form = modal.querySelector("[data-pw-form]");
    var curEl = modal.querySelector("[data-pw-cur]");
    var newEl = modal.querySelector("[data-pw-new]");
    var confirmEl = modal.querySelector("[data-pw-confirm]");
    var submitBtn = modal.querySelector("[data-pw-submit]");

    function open() {
      curEl.value = ""; newEl.value = ""; confirmEl.value = "";
      hideError();
      successBox.classList.add("hidden");
      formWrap.classList.remove("hidden");
      modal.classList.remove("hidden");
      modal.classList.add("flex");
      var drawer = document.querySelector(".mobile-drawer");
      if (drawer) drawer.classList.add("hidden");
    }
    function close() { modal.classList.add("hidden"); modal.classList.remove("flex"); }
    function showError(msg) { errEl.textContent = msg; errEl.classList.remove("hidden"); }
    function hideError() { errEl.textContent = ""; errEl.classList.add("hidden"); }

    document.querySelectorAll("[data-pw-open]").forEach(function (b) { b.addEventListener("click", open); });
    modal.querySelectorAll("[data-pw-close]").forEach(function (b) { b.addEventListener("click", close); });

    modal.querySelectorAll("[data-pw-toggle]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var which = btn.getAttribute("data-pw-toggle");
        if (which === "cur") { curEl.type = curEl.type === "password" ? "text" : "password"; }
        else { var t = newEl.type === "password" ? "text" : "password"; newEl.type = t; confirmEl.type = t; }
      });
    });

    form.addEventListener("submit", function (e) {
      e.preventDefault();
      hideError();
      var cur = curEl.value, nw = newEl.value, cf = confirmEl.value;
      if (!cur || !nw || !cf) { showError("请填写所有字段"); return; }
      if (nw.length < 6) { showError("新密码至少 6 位"); return; }
      if (nw !== cf) { showError("两次输入的新密码不一致"); return; }
      if (nw === cur) { showError("新密码不能与当前密码相同"); return; }

      submitBtn.disabled = true; submitBtn.textContent = "提交中…";
      fetch(BG_BASE + "/api/change-password.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ currentPassword: cur, newPassword: nw })
      }).then(function (res) {
        return res.json().catch(function () { return {}; }).then(function (data) {
          submitBtn.disabled = false; submitBtn.textContent = "确认修改";
          if (!res.ok) { showError(data.error || "修改失败"); return; }
          formWrap.classList.add("hidden");
          successBox.classList.remove("hidden");
        });
      }).catch(function () {
        submitBtn.disabled = false; submitBtn.textContent = "确认修改";
        showError("网络异常，请重试");
      });
    });
  }

  /* ============ 内容编辑器 ============ */
  function initContentEditor() {
    var root = document.querySelector("[data-content-editor]");
    var dataEl = document.getElementById("bg-editor-data");
    if (!root || !dataEl) return;

    var def;
    try { def = JSON.parse(dataEl.textContent); } catch (e) { return; }
    var items = def.items || [];
    var editing = null;        // null | "new" | id
    var form = {};
    var error = "";
    var uploadTarget = null;

    var inputCls = "mt-1.5 w-full rounded-md border border-input bg-background px-3 py-2 text-sm outline-none focus:border-brand";

    function blankForm() {
      var f = {};
      def.fields.forEach(function (field) {
        if (field.type === "stringlist") f[field.key] = [];
        else if (field.type === "json") f[field.key] = "[]";
        else if (field.bilingual) f[field.key] = { zh: "", en: "" };
        else f[field.key] = "";
      });
      return f;
    }

    function recordToForm(item) {
      var f = {};
      def.fields.forEach(function (field) {
        var v = item[field.key];
        if (field.type === "stringlist") {
          if (field.bilingual) {
            f[field.key] = Array.isArray(v) ? v.map(function (it) {
              if (typeof it === "string") return { zh: it, en: "" };
              if (it && typeof it === "object" && "zh" in it) return { zh: it.zh || "", en: it.en || "" };
              return { zh: "", en: "" };
            }) : [];
          } else {
            f[field.key] = Array.isArray(v) ? v.slice() : [];
          }
        } else if (field.type === "json") {
          f[field.key] = JSON.stringify(v == null ? [] : v, null, 2);
        } else if (field.bilingual) {
          if (typeof v === "string") f[field.key] = { zh: v, en: "" };
          else if (v && typeof v === "object" && "zh" in v) f[field.key] = { zh: v.zh || "", en: v.en || "" };
          else f[field.key] = { zh: "", en: "" };
        } else {
          f[field.key] = v == null ? "" : v;
        }
      });
      return f;
    }

    function displayValue(v) {
      if (typeof v === "string") return v;
      if (v && typeof v === "object" && "zh" in v) return v.zh || "(无标题)";
      return v == null ? "" : String(v);
    }

    function isUploadField(key) { return key === "videoUrl" || key === "poster" || key === "logo"; }
    function uploadDirForField(key) { return key === "videoUrl" ? "videos" : (key === "logo" ? "logos" : "posters"); }

    /* ---------- 渲染 ---------- */
    function render() {
      root.innerHTML = "";
      if (editing) { renderForm(); }
      else if (def.singleton) { renderSingleton(); }
      else { renderList(); }
    }

    function headerHtml() {
      var right = def.singleton ? "" :
        '<button type="button" data-new class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90">＋ 新增' + esc(def.singular) + "</button>";
      var sub = def.singleton ? "该内容为单条设置" : ("共 " + items.length + " 条");
      return '<div class="mb-6 flex items-center justify-between"><div><h1 class="text-xl font-bold">' + esc(def.label) + '管理</h1><p class="text-sm text-muted-foreground">' + esc(sub) + '</p></div>' + right + '</div>';
    }

    function errorHtml() {
      return error ? '<div class="mb-4 rounded-md border border-red-500/30 bg-red-500/10 px-3 py-2 text-sm text-red-600">' + esc(error) + "</div>" : "";
    }

    function renderList() {
      var html = headerHtml() + errorHtml();
      if (items.length === 0) {
        html += '<p class="text-sm text-muted-foreground">暂无内容，点击右上角新增。</p>';
      } else {
        html += '<div class="space-y-3">';
        items.forEach(function (item) {
          var first = displayValue(item[def.fields[0].key]);
          var second = def.fields[1] ? displayValue(item[def.fields[1].key]) : "";
          html += '<div class="flex items-center justify-between rounded-xl border border-border bg-card px-4 py-3">' +
            '<div class="min-w-0 text-sm"><span class="font-medium text-foreground">' + esc(first) + "</span>" +
            (def.fields[1] ? '<span class="ml-2 truncate text-muted-foreground">' + esc(second) + "</span>" : "") +
            '</div><div class="flex shrink-0 gap-2">' +
            '<button type="button" data-edit="' + esc(item.id) + '" class="rounded-md border border-border px-3 py-1.5 text-sm font-medium">编辑</button>' +
            '<button type="button" data-del="' + esc(item.id) + '" class="rounded-md border border-border px-3 py-1.5 text-sm font-medium text-red-600">删除</button>' +
            "</div></div>";
        });
        html += "</div>";
      }
      root.innerHTML = html;
      var nb = root.querySelector("[data-new]");
      if (nb) nb.addEventListener("click", startNew);
      root.querySelectorAll("[data-edit]").forEach(function (b) {
        b.addEventListener("click", function () { startEdit(findItem(b.getAttribute("data-edit"))); });
      });
      root.querySelectorAll("[data-del]").forEach(function (b) {
        b.addEventListener("click", function () { remove(b.getAttribute("data-del")); });
      });
    }

    function renderSingleton() {
      var html = headerHtml() + errorHtml() +
        '<div class="rounded-2xl border border-border bg-card p-6"><button type="button" data-edit-single class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90">编辑' + esc(def.singular) + "</button></div>";
      root.innerHTML = html;
      root.querySelector("[data-edit-single]").addEventListener("click", function () {
        startEdit(items[0] || { id: "singleton" });
      });
    }

    function findItem(id) {
      for (var i = 0; i < items.length; i++) { if (String(items[i].id) === String(id)) return items[i]; }
      return null;
    }

    function renderForm() {
      var html = errorHtml() +
        '<div class="space-y-4 rounded-2xl border border-border bg-card p-6"><h2 class="font-semibold">' +
        (editing === "new" ? "新增" : "编辑") + esc(def.singular) + "</h2>";
      def.fields.forEach(function (field) {
        html += '<div data-field="' + esc(field.key) + '"><label class="text-sm font-medium text-foreground">' + esc(field.label) + "</label>";
        html += fieldControlHtml(field);
        html += "</div>";
      });
      html += '<div class="flex gap-3 pt-2"><button type="button" data-save class="rounded-md bg-brand px-6 py-2.5 text-sm font-medium text-brand-foreground transition-opacity hover:opacity-90">保存</button>' +
        '<button type="button" data-cancel class="rounded-md border border-border px-6 py-2.5 text-sm font-medium">取消</button></div></div>';
      root.innerHTML = html;
      bindFormEvents();
    }

    function fieldControlHtml(field) {
      var key = field.key, v = form[key];
      if (field.type === "text" && field.bilingual) {
        return '<div class="mt-1.5 grid gap-2 sm:grid-cols-2">' +
          '<input class="' + inputCls + '" placeholder="中文" data-bi="zh" value="' + esc(v && v.zh) + '">' +
          '<input class="' + inputCls + '" placeholder="English" data-bi="en" value="' + esc(v && v.en) + '"></div>';
      }
      if (field.type === "text" && !field.bilingual) {
        var upload = isUploadField(key) ? '<button type="button" data-upload class="shrink-0 rounded-md border border-border bg-background px-3 text-sm font-medium hover:bg-muted">上传</button>' : "";
        return '<div class="mt-1.5 flex gap-2"><input class="' + inputCls + ' flex-1" data-single value="' + esc(v) + '">' + upload + "</div>";
      }
      if (field.type === "textarea" && field.bilingual) {
        return '<div class="mt-1.5 grid gap-2 sm:grid-cols-2">' +
          '<textarea rows="4" class="' + inputCls + '" placeholder="中文" data-bi="zh">' + esc(v && v.zh) + "</textarea>" +
          '<textarea rows="4" class="' + inputCls + '" placeholder="English" data-bi="en">' + esc(v && v.en) + "</textarea></div>";
      }
      if (field.type === "textarea" && !field.bilingual) {
        return '<textarea rows="4" class="' + inputCls + '" data-single>' + esc(v) + "</textarea>";
      }
      if (field.type === "json") {
        return '<textarea rows="6" class="' + inputCls + ' font-mono text-xs" data-single>' + esc(v) + "</textarea>";
      }
      if (field.type === "stringlist" && field.bilingual) {
        var rows = "";
        (v || []).forEach(function (item, idx) {
          rows += '<div class="flex gap-2" data-row="' + idx + '">' +
            '<input class="' + inputCls + '" placeholder="中文" data-list-bi="zh" value="' + esc(item.zh) + '">' +
            '<input class="' + inputCls + '" placeholder="English" data-list-bi="en" value="' + esc(item.en) + '">' +
            '<button type="button" data-list-remove="' + idx + '" class="shrink-0 rounded-md border border-border px-3 text-sm text-red-600">删除</button></div>';
        });
        return '<div class="space-y-2" data-list>' + rows +
          '<button type="button" data-list-add class="rounded-md border border-border px-3 py-1.5 text-sm font-medium">＋ 添加一项</button></div>';
      }
      if (field.type === "stringlist" && !field.bilingual) {
        var rows2 = "";
        (v || []).forEach(function (val, idx) {
          rows2 += '<div class="flex gap-2" data-row="' + idx + '">' +
            '<input class="' + inputCls + '" data-list-single value="' + esc(val) + '">' +
            '<button type="button" data-list-remove="' + idx + '" class="shrink-0 rounded-md border border-border px-3 text-sm text-red-600">删除</button></div>';
        });
        return '<div class="space-y-2" data-list>' + rows2 +
          '<button type="button" data-list-add class="rounded-md border border-border px-3 py-1.5 text-sm font-medium">＋ 添加一项</button></div>';
      }
      return "";
    }

    function bindFormEvents() {
      def.fields.forEach(function (field) {
        var wrap = root.querySelector('[data-field="' + field.key + '"]');
        if (!wrap) return;
        var key = field.key;

        if ((field.type === "text" || field.type === "textarea") && field.bilingual) {
          wrap.querySelectorAll("[data-bi]").forEach(function (el) {
            el.addEventListener("input", function () {
              if (!form[key] || typeof form[key] !== "object") form[key] = { zh: "", en: "" };
              form[key][el.getAttribute("data-bi")] = el.value;
            });
          });
        } else if ((field.type === "text" || field.type === "textarea" || field.type === "json") && !field.bilingual) {
          var single = wrap.querySelector("[data-single]");
          if (single) single.addEventListener("input", function () { form[key] = single.value; });
          var up = wrap.querySelector("[data-upload]");
          if (up) up.addEventListener("click", function () { triggerUpload(key); });
        } else if (field.type === "stringlist") {
          bindListEvents(wrap, field);
        }
      });
      root.querySelector("[data-save]").addEventListener("click", save);
      root.querySelector("[data-cancel]").addEventListener("click", cancel);
    }

    function bindListEvents(wrap, field) {
      var key = field.key;
      wrap.querySelectorAll("[data-row]").forEach(function (rowEl) {
        var idx = parseInt(rowEl.getAttribute("data-row"), 10);
        if (field.bilingual) {
          rowEl.querySelectorAll("[data-list-bi]").forEach(function (el) {
            el.addEventListener("input", function () {
              form[key][idx][el.getAttribute("data-list-bi")] = el.value;
            });
          });
        } else {
          var s = rowEl.querySelector("[data-list-single]");
          if (s) s.addEventListener("input", function () { form[key][idx] = s.value; });
        }
        rowEl.querySelector("[data-list-remove]").addEventListener("click", function () {
          form[key].splice(idx, 1);
          rerenderField(field);
        });
      });
      wrap.querySelector("[data-list-add]").addEventListener("click", function () {
        form[key].push(field.bilingual ? { zh: "", en: "" } : "");
        rerenderField(field);
      });
    }

    function rerenderField(field) {
      var wrap = root.querySelector('[data-field="' + field.key + '"]');
      if (!wrap) return;
      var label = wrap.querySelector("label").outerHTML;
      wrap.innerHTML = label + fieldControlHtml(field);
      if (field.type === "stringlist") bindListEvents(wrap, field);
    }

    /* ---------- 上传 ---------- */
    var fileInput = document.createElement("input");
    fileInput.type = "file";
    fileInput.style.display = "none";
    document.body.appendChild(fileInput);
    fileInput.addEventListener("change", function () {
      var file = fileInput.files && fileInput.files[0];
      if (!file || !uploadTarget) return;
      var dir = uploadDirForField(uploadTarget);
      var fd = new FormData();
      fd.append("file", file);
      fd.append("dir", dir);
      var target = uploadTarget;
      fetch(BG_BASE + "/api/upload.php", { method: "POST", body: fd }).then(function (res) {
        if (!res.ok) {
          return res.text().catch(function () { return ""; }).then(function (t) {
            error = "上传失败：" + t; uploadTarget = null; fileInput.value = ""; render();
          });
        }
        return res.json().then(function (data) {
          form[target] = data.url;
          uploadTarget = null; fileInput.value = "";
          var wrap = root.querySelector('[data-field="' + target + '"] [data-single]');
          if (wrap) wrap.value = data.url;
        });
      }).catch(function () { error = "上传失败：网络异常"; uploadTarget = null; fileInput.value = ""; render(); });
    });

    function triggerUpload(key) {
      uploadTarget = key;
      fileInput.accept = key === "videoUrl" ? "video/*" : "image/*";
      fileInput.click();
    }

    /* ---------- 操作 ---------- */
    function startNew() { error = ""; editing = "new"; form = blankForm(); render(); }
    function startEdit(item) { if (!item) return; error = ""; editing = String(item.id != null ? item.id : "singleton"); form = recordToForm(item); render(); }
    function cancel() { editing = null; form = {}; error = ""; render(); }

    function save() {
      error = "";
      var payload = {};
      Object.keys(form).forEach(function (k) { payload[k] = form[k]; });
      var ok = true;
      def.fields.forEach(function (field) {
        if (field.type === "json") {
          try { payload[field.key] = JSON.parse(String(form[field.key] || "[]")); }
          catch (e) { error = "字段「" + field.label + "」不是合法 JSON"; ok = false; }
        }
      });
      if (!ok) { render(); return; }

      var isNew = editing === "new";
      var url = BG_BASE + "/api/content.php?type=" + def.type;
      var method = "PUT";
      if (!def.singleton) {
        url = isNew ? url : url + "&id=" + editing;
        method = isNew ? "POST" : "PUT";
      }

      fetch(url, { method: method, headers: { "Content-Type": "application/json" }, body: JSON.stringify(payload) })
        .then(function (res) {
          if (!res.ok) {
            return res.text().catch(function () { return ""; }).then(function (t) { error = "保存失败：" + t; render(); });
          }
          return fetch(BG_BASE + "/api/content.php?type=" + def.type).then(function (r) { return r.json(); }).then(function (list) {
            items = def.singleton ? (list.data ? [list.data] : []) : (list.data || []);
            editing = null; form = {}; render();
          });
        }).catch(function () { error = "保存失败：网络异常"; render(); });
    }

    function remove(id) {
      if (!window.confirm("确认删除该项？")) return;
      fetch(BG_BASE + "/api/content.php?type=" + def.type + "&id=" + id, { method: "DELETE" }).then(function (res) {
        if (!res.ok) { window.alert("删除失败"); return; }
        items = items.filter(function (i) { return String(i.id) !== String(id); });
        render();
      }).catch(function () { window.alert("删除失败"); });
    }

    render();
  }

  /* ============ 在线更新 ============ */
  function initUpdate() {
    var page = document.querySelector("[data-update-page]");
    if (!page) return;
    var remoteBox = document.getElementById("update-remote");
    var logBox = document.getElementById("update-log");
    var releasesBox = document.getElementById("update-releases");
    var checkBtns = page.querySelectorAll("[data-check]");
    var doBtn = page.querySelector("[data-do]");

    function getFull() {
      var sel = page.querySelector('input[name="upd-mode"]:checked');
      return sel ? sel.value === "full" : false;
    }
    function setLog(msg, isErr) {
      logBox.textContent = msg;
      logBox.className = "mt-4 rounded-md border px-3 py-2 text-sm " + (isErr
        ? "border-red-500/30 bg-red-500/10 text-red-600"
        : "border-green-500/30 bg-green-500/10 text-green-700");
    }

    function renderReleases(d) {
      if (!releasesBox) return;
      var list = d.releases || [];
      if (!list.length) {
        releasesBox.innerHTML = '<p class="text-tech-muted">未获取到版本列表。</p>';
        return;
      }
      var curVer = (d.current && d.current.version) || "";
      var curIdx = -1;
      list.forEach(function (r, i) { if (r.tag === curVer) curIdx = i; });
      var html = "";
      list.forEach(function (r, i) {
        var isCur = r.tag === curVer;
        var actionLabel = "更新到该版本";
        if (isCur) actionLabel = "当前版本";
        else if (curIdx !== -1 && i > curIdx) actionLabel = "回滚到该版本";
        var bodyText = (r.body || "").trim();
        // 避免把 GitHub 自动生成的 Markdown 整段塞进 <pre>，简单转义即可
        html += '<div class="rounded-xl border border-[#0c1426]/10 bg-[#0c1426]/[0.02] p-4">';
        html += '<div class="flex flex-wrap items-center justify-between gap-3">';
        html += '<div><p class="font-semibold text-tech-ink">' + esc(r.tag) + (isCur ? ' <span class="ml-2 rounded-full bg-tech-blue/10 px-2 py-0.5 text-xs text-tech-blue">当前</span>' : '') + '</p>';
        html += '<p class="mt-0.5 text-xs text-tech-muted">' + esc(r.published_at || "") + (r.name && r.name !== r.tag ? " · " + esc(r.name) : "") + '</p></div>';
        if (!isCur) {
          html += '<button type="button" class="rounded-lg bg-brand px-3 py-1.5 text-xs font-medium text-brand-foreground transition-opacity hover:opacity-90" data-update-tag="' + esc(r.tag) + '" data-action="' + esc(actionLabel) + '">' + esc(actionLabel) + '</button>';
        } else {
          html += '<span class="text-xs text-tech-muted">已安装</span>';
        }
        html += '</div>';
        if (bodyText) {
          html += '<pre class="mt-3 max-h-64 overflow-auto whitespace-pre-wrap break-words rounded-md bg-white/70 p-3 text-xs leading-relaxed text-tech-muted">' + esc(bodyText) + '</pre>';
        }
        html += '</div>';
      });
      releasesBox.innerHTML = html;

      Array.prototype.forEach.call(releasesBox.querySelectorAll("[data-update-tag]"), function (btn) {
        btn.addEventListener("click", function () {
          var tag = btn.getAttribute("data-update-tag");
          var label = btn.getAttribute("data-action") || "更新到该版本";
          if (!window.confirm("确认" + label + "（" + tag + "）？更新前会自动备份将被覆盖的文件（data/uploads 默认保留）。")) return;
          btn.disabled = true; btn.textContent = "处理中…";
          setLog("正在下载并应用 " + tag + "，请稍候…", false);
          fetch(BG_BASE + "/api/update.php?action=do", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ tag: tag, full: getFull() })
          })
            .then(function (r) { return r.json().catch(function () { return {}; }); })
            .then(function (dd) {
              btn.disabled = false; btn.textContent = label;
              if (!dd || !dd.ok) { setLog(label + "失败：" + esc(dd.error || "未知错误"), true); return; }
              var res = dd.result || {};
              setLog(label + "成功！已覆盖 " + (res.covered || 0) + " 个文件，备份 " + (res.backed || 0) + " 个到 " + esc(res.backup || ""), false);
              // 刷新一次版本信息
              doCheck();
            })
            .catch(function () {
              btn.disabled = false; btn.textContent = label;
              setLog("网络异常：请求可能仍在后台执行，请稍后用「检查更新」核对版本。", true);
            });
        });
      });
    }

    function doCheck() {
      Array.prototype.forEach.call(checkBtns, function (b) {
        b.disabled = true;
        if (b.tagName === "BUTTON") b.textContent = (b.textContent || "检查更新").replace("检查中…", "检查更新").indexOf("刷新") !== -1 ? "刷新中…" : "检查中…";
      });
      fetch(BG_BASE + "/api/update.php?action=check")
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (d) {
          Array.prototype.forEach.call(checkBtns, function (b) {
            b.disabled = false;
            if (b.tagName === "BUTTON") b.textContent = (b.textContent.indexOf("刷新") !== -1) ? "刷新" : "检查更新";
          });
          if (!d || !d.ok) { remoteBox.innerHTML = '<span class="text-red-600">检查失败：' + esc(d.error || "未知错误") + "</span>"; return; }
          var cur = d.current && d.current.version ? d.current.version : "unknown";
          var rm = d.remote || {};
          remoteBox.innerHTML =
            '<p class="text-tech-ink">当前：<b>' + esc(cur) + "</b></p>" +
            '<p class="mt-1">最新：<b>' + esc(rm.tag || "-") + "</b>" + (rm.published_at ? " <span class=\"text-tech-muted\">(" + esc(rm.published_at) + ")</span>" : "") + "</p>";
          renderReleases(d);
        })
        .catch(function () {
          Array.prototype.forEach.call(checkBtns, function (b) {
            b.disabled = false;
            if (b.tagName === "BUTTON") b.textContent = (b.textContent.indexOf("刷新") !== -1) ? "刷新" : "检查更新";
          });
          remoteBox.innerHTML = '<span class="text-red-600">网络异常，请重试</span>';
        });
    }

    Array.prototype.forEach.call(checkBtns, function (b) {
      b.addEventListener("click", doCheck);
    });

    doBtn.addEventListener("click", function () {
      if (!window.confirm("确认更新？更新前会自动备份将被覆盖的文件（data/uploads 默认保留）。")) return;
      doBtn.disabled = true; doBtn.textContent = "更新中…（请勿关闭页面）";
      setLog("正在下载并应用更新，请稍候…", false);
      fetch(BG_BASE + "/api/update.php?action=do", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ full: getFull() })
      })
        .then(function (r) { return r.json().catch(function () { return {}; }); })
        .then(function (d) {
          doBtn.disabled = false; doBtn.textContent = "立即更新";
          if (!d || !d.ok) { setLog("更新失败：" + esc(d.error || "未知错误"), true); return; }
          var res = d.result || {};
          setLog("更新成功！已覆盖 " + (res.covered || 0) + " 个文件，备份 " + (res.backed || 0) + " 个到 " + esc(res.backup || ""), false);
          doCheck();
        })
        .catch(function () {
          doBtn.disabled = false; doBtn.textContent = "立即更新";
          setLog("网络异常：请求可能仍在后台执行，请稍后用「检查更新」核对版本。", true);
        });
    });
  }

  document.addEventListener("DOMContentLoaded", function () {
    initDrawer();
    initLogout();
    initPwModal();
    initContentEditor();
    initUpdate();
  });
})();
