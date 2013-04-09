<?php

/**
 * POST类
 * @author fotomxq <fotomxq.me>
 * @version 1
 * @package oa
 */
class oapost {

    /**
     * 表名称
     * @since 1
     * @var string 
     */
    private $table_name;

    /**
     * 数据库操作句柄
     * @since 1
     * @var coredb 
     */
    private $db;

    /**
     * 操作IP ID
     * @since 1
     * @var int
     */
    private $ip_id;

    /**
     * 字段列表
     * @since 1
     * @var array 
     */
    private $fields;

    /**
     * 初始化
     * @since 1
     * @param coredb $db 数据库操作句柄
     * @param int $ip_id IP ID
     */
    public function __construct(&$db, $ip_id) {
        $this->db = $db;
        $this->table_name = $db->tables['posts'];
        $this->ip_id = $ip_id;
        $this->fields = array('id', 'post_title', 'post_content', 'post_date', 'post_modified', 'post_ip', 'post_type', 'post_order', 'post_parent', 'post_user', 'post_password', 'post_name', 'post_url', 'post_status', 'post_meta');
    }

    /**
     * 查询列表
     * @since 1
     * @param string $user 用户ID
     * @param string $title 搜索标题
     * @param string $content 搜索内容
     * @param string $status 状态 public|private|trush
     * @param string $type 识别类型 text|picture|file
     * @param int $page 页数
     * @param int $max 页长
     * @param int $sort 排序字段键值
     * @param boolean $desc 是否倒序
     * @return boolean
     */
    public function view_list($user = null, $title = null, $content = null, $status = 'public', $type = 'text', $page = 1, $max = 10, $sort = 7, $desc = true) {
        $sql_where = '';
        if ($title) {
            $title = '%' . $title . '%';
            $sql_where .= ' OR `post_title`=:title';
        }
        if ($content) {
            $content = '%' . $content . '%';
            $sql_where .= ' OR `post_content`=:content';
        }
        if ($sql_where) {
            $sql_where = substr($sql_where, 4);
        }
        $sql_desc = $desc ? 'DESC' : 'ASC';
        $sql = 'SELECT `' . implode('`,`', $this->fields) . '` FROM `' . $this->table_name . '` WHERE ' . $sql_where . ' AND `post_status`=:status AND `post_user`=:user AND `post_type`=:type ORDER BY ' . $this->fields[$sort] . ',' . $this->fields[0] . ' ' . $sql_desc . ' LIMIT ' . ($page - 1) * $max . ',' . $max;
        ;
        $sth = $this->db->prepare($sql);
        if ($title) {
            $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($content) {
            $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetchAll(PDO::FETCH_ASSOC);
        }
        return false;
    }

    /**
     * 获取条件下的记录数
     * @since 1
     * @param string $user 用户ID
     * @param string $title 搜索标题
     * @param string $content 搜索内容
     * @param string $status 状态 public|private|trush
     * @param string $type 识别类型 text|picture|file
     * @return boolean
     */
    public function view_list_row($user = null, $title = null, $content = null, $status = 'public', $type = 'text') {
        $sql_where = '';
        if ($title) {
            $title = '%' . $title . '%';
            $sql_where .= ' OR `post_title`=:title';
        }
        if ($content) {
            $content = '%' . $content . '%';
            $sql_where .= ' OR `post_content`=:content';
        }
        if ($sql_where) {
            $sql_where = substr($sql_where, 4);
        }
        $sql = 'SELECT COUNT(id) FROM `' . $this->table_name . '` WHERE ' . $sql_where . ' AND `post_status`=:status AND `post_user`=:user AND `post_type`=:type';
        $sth = $this->db->prepare($sql);
        if ($title) {
            $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        if ($content) {
            $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        }
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetchColumn();
        }
        return false;
    }

    /**
     * 查询ID
     * @since 1
     * @param int $id 主键
     * @return boolean|array
     */
    public function view($id) {
        $return = false;
        if ($this->check_int($int) == false) {
            return $return;
        }
        $sql = 'SELECT `' . implode('`,`', $this->fields) . '` FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $sth->fetch(PDO::FETCH_ASSOC);
        }
        return $return;
    }

    /**
     * 添加新的记录
     * @since 1
     * @param string $title 标题
     * @param string $content 内容
     * @param string $type 类型
     * @param int $parent 上一级ID
     * @param int $user 用户ID
     * @param string $pw 密码明文
     * @param string $name 媒体文件原名称
     * @param string $url 媒体路径或内容访问路径
     * @param string $status 状态 public|private|trash
     * @param string $meta 媒体文件访问头信息
     * @return int 0或记录ID
     */
    public function add($title, $content, $type, $parent, $user, $pw, $name, $url, $status, $meta) {
        $return = 0;
        $sql = 'INSERT INTO `' . $this->table_name . '`(`' . implode('`,`', $this->fields) . '`) VALUES(NULL,:title,:content,NOW(),NULL,:ip,:type,:parent,:user,:pw,:name,:url,:status,:meta)';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':ip', $this->ip_id, PDO::PARAM_INT);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':pw', sha1($pw), PDO::PARAM_STR);
        $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':url', $url, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':meta', $meta, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = $this->db->lastInsertId();
        }
        return $return;
    }

    /**
     * 编辑记录
     * @since 1
     * @param int $id 主键
     * @param string $title 标题
     * @param string $content 内容
     * @param string $type 类型
     * @param int $parent 上一级ID
     * @param int $user 用户ID
     * @param string $pw 密码明文
     * @param string $name 媒体文件原名称
     * @param string $url 媒体路径或内容访问路径
     * @param string $status 状态 public|private|trash
     * @param string $meta 媒体文件访问头信息
     * @return boolean
     */
    public function edit($id, $title, $content, $type, $parent, $user, $pw, $name, $url, $status, $meta) {
        $return = false;
        $sql = 'UPDATE `' . $this->table_name . '` SET `post_title`=:title,`post_content`=:content,`post_type`=:type,`post_parent`=:parent,`post_user`=:user,`post_password`=:pw,`post_name`=:name,`post_url`=:url,`post_status`=:status,`post_meta`=:meta WHERE `id`=:id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':title', $title, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':content', $content, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':type', $type, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':parent', $parent, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':user', $user, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':pw', sha1($pw), PDO::PARAM_STR);
        $sth->bindParam(':name', $name, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':url', $url, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':status', $status, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':meta', $meta, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        if ($sth->execute() == true) {
            $return = true;
        }
        return $return;
    }

    /**
     * 删除post
     * @since 1
     * @param int $id 主键
     * @return boolean
     */
    public function del($id) {
        if ($this->check_int($int) == false) {
            return false;
        }
        $sql = 'DELETE FROM `' . $this->table_name . '` WHERE `id` = :id';
        $sth = $this->db->prepare($sql);
        $sth->bindParam(':id', $id, PDO::PARAM_INT | PDO::PARAM_INPUT_OUTPUT);
        return $sth->execute();
    }

    /**
     * 过滤数字
     * @since 1
     * @param int $int
     * @return int|boolean
     */
    private function check_int($int) {
        return filter_var($int, FILTER_VALIDATE_INT);
    }

}

?>