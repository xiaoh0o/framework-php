<?php
namespace App\Core\Utils;

use App\Core\ValueObject;

/**
 * 分页数据
 */
class Pagination extends ValueObject
{
    const TYPE_ALL  = 0;
    const TYPE_PART = 1;

    protected int $pagecount;

    public function __construct(
        protected int $type,
        protected int $page,
        protected int $pagesize,
        protected int $total,
        protected int $number,
        protected string $url,
        protected array $params,
        protected array $extends
    ) {
        $this->pagecount = $this->pagecount($type, $page, $pagesize, $total);

        // 验证当前页数
        if ($this->page > $this->pagecount && $this->pagecount > 0) {
            $this->page = $this->pagecount;
        }
    }

    public static function build(array $data)
    {
        $type = self::TYPE_ALL;
        if (isset($data['type'])) {
            if ($data['type'] == self::TYPE_PART || (string)$data['type'] == "part") {
                $type = self::TYPE_PART;
            }
        }

        if (!Math::isInt($data['page']) || $data['page'] <= 0) {
            $page = 1;
        } else {
            $page = intval($data['page']);
        }

        if (!isset($data['pagesize']) || !Math::isInt($data['pagesize']) || $data['pagesize'] <= 0) {
            $pagesize = 20;
        } else {
            $pagesize = intval($data['pagesize']);
        }

        // 设置默认分页数
        if (!isset($data['number']) || !Math::isInt($data['number']) || $data['number'] <= 0) {
            $number = 4;
        } else {
            $number = intval($data['number']);
        }

        $total = intval($data['total']);
        $url = rtrim($data['url'], "?");

        if (!isset($data['params']) || !is_array($data['params'])) {
            $params = [];
        } else {
            if (isset($data['params']['page'])) {
                unset($data['params']['page']);
            }
            $params = $data['params'];
        }

        // 清掉 params 中的空值
        foreach ($params as $key => $value) {
            if ($value === "") {
                unset($params[$key]);
            }
        }

        if (!isset($data['extends'])) {
            $extends = [];
        } else {
            $extends = (array)$data['extends'];
        }

        return parent::build([
            "type"      => $type,
            "page"      => $page,
            "pagesize"  => $pagesize,
            "total"     => $total,
            "number"    => $number,
            "url"       => $url,
            "params"    => $params,
            "extends"   => $extends,
        ]);
    }

    // 创建连接地址
    public function link(int $page)
    {
        $params = $this->params;
        $params['page'] = $page;
        $query = http_build_query($this->params);
        return $this->url ."?{$query}";
    }

    // 是否有总记录数
    public function isTotal()
    {
        return ($this->type == self::TYPE_ALL);
    }

    // 总页数
    protected function pagecount(int $type, int $page, int $pagesize, int $total)
    {
        $pagecount = 0;
        if ($total > 0) {
            if (($total % $pagesize) == 0) {
                $pagecount = intval($total / $pagesize);
            } else {
                $pagecount = intval(ceil($total / $pagesize));
            }
        }
        if ($type == self::TYPE_PART) {
            // 这里的 {$pagecount} 仅是 当前页后的页数
            $pagecount += $page;
        }

        return $pagecount;
    }
}
