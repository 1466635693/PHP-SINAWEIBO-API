<?php
class weiboApi
{
    
    //微博登录
    const SINA_WEIBO_LOGIN_URL = 'http://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.18)';
    //微博预登录获取参数
    const SINA_WEIBO_PRELOGIN_URL = 'http://login.sina.com.cn/sso/prelogin.php';
    //微博发微博与话题
    const SINA_WEIBO_ADD_WEIBO_URL = 'http://weibo.com/aj/mblog/add?ajwvr=6&__rnd=%replace%';
    
    //话题关注
    const SINA_WEIBO_FOLLOW_URL = 'http://weibo.com/p/aj/relation/follow?ajwvr=6&__rnd=%replace%';
    
    //热点、实时热搜
    const SINA_WEIBO_HOT_URL = 'http://s.weibo.com/top/summary?Refer=top_hot&topnav=1&wvr=6';
    
    //1小时热点 24小时热点 
    const SINA_WEIBO_NOW_URL = 'http://d.weibo.com/100803?pids=Pl_Discover_Pt6Rank__5&cfs=&Pl_Discover_Pt6Rank__5_filter=hothtlist_type%3D#type#&ajaxpagelet=1&__ref=/100803&_t=FM_#time#';
    
    //微博点赞
    const SINA_WEIBO_BELIKE_URL = 'http://s.weibo.com/ajax/mblog/like/add?__rnd=#time#';
    
    //搜索微博
    const SINA_WEIBO_SEARCH_URL = 'http://s.weibo.com/weibo/#content#&page=#num#';
    
    //评论微博
    const SINA_WEIBO_COMMENT_URL = 'http://s.weibo.com/ajax/comment/add?__rnd=#time#';
    
    //转发微博
    const SINA_WEIBO_FORWARD_URL = 'http://weibo.com/aj/v6/mblog/forward?ajwvr=6&domain=#userid#&__rnd=#time#';
     
    //新浪微博表情包
    protected $allFace = array(
       '[发红包啦]', '[抢到啦]', '[最右]', '[泪流满面]', '[江南style]', '[偷乐]', '[加油啊]',
       '[doge]', '[喵喵]', '[笑cry]', '[xkl转圈]', '[芒果得意]', '[微笑]', '[嘻嘻]', '[哈哈]',
       '[可爱]', '[可怜]', '[挖鼻]', '[吃惊]', '[害羞]', '[挤眼]', '[闭嘴]', '[鄙视]', '[爱你]',
       '[泪]', '[偷笑]', '[亲亲]','[生病]','[太开心]','[白眼]','[右哼哼]','[左哼哼]','[嘘]',
        '[衰]','[委屈]','[吐]','[哈欠]','[抱抱]','[怒]','[疑问]','[馋嘴]','[拜拜]','[思考]',
        '[汗]','[困]','[睡]','[钱]','[失望]','[酷]','[色]','[哼]','[鼓掌]','[晕]','[悲伤]',
        '[抓狂]','[黑线]','[阴险]','[怒骂]','[互粉]','[心]','[伤心]','[猪头]','[熊猫]',
        '[兔子]','[ok]','[耶]','[good]','[NO]','[赞]','[来]','[弱]','[草泥马]',
        '[神马]','[囧]','[浮云]','[给力]','[围观]','[威武]','[奥特曼]','[礼物]',
        '[钟]','[话筒]','[蜡烛]','[蛋糕]'
    );
    
    protected  $servertime; //登录参数
    protected  $pcid; //登录参数
    protected  $nonce;//登录参数
    protected  $pubkey;//登录参数
    protected  $rsakv;//登录参数
    protected  $exectime;//登录参数
    protected  $cookieFile;//cookie文件路径
    protected  $userid; //新浪微博返回的用户id
    protected  $pythonFile; //python文件路径
    public function __construct()
    {
        $this->cookieFile = REAL_APP_PATH.'cookie/cookie.txt';
        $this->pythonFile = REAL_APP_PATH.'python/getPubkey.py';
    }
    
    /**
         * 新浪微博登录.
         *
         * @access public
         * @param  string   $user 微博用户名
         * @param  string   $pwd  微博密码
         * @return boolean  登录成功后返回微博用户id
         */          
    public function login($user, $pwd)
    {
        
        //对用户名和密码进行加密
        if(empty($user) || empty($pwd)) return false;
        
        if(!$this->prelogin())
        {
            return false;
        }
        $exec = "python {$this->pythonFile} {$this->pubkey} {$this->servertime} {$this->nonce} {$pwd}";
        
        exec($exec, $rsaKeyArray);
        if(empty($rsaKeyArray[0]))
        {
            return false;
        }
        preg_match('/\'(.*?)\'/', $rsaKeyArray[0], $sp);
        if(empty($sp[1]))
        {
            return false;
        }
        $su = base64_encode(urlencode($user));
        $sp = $sp[1];
        
        
        $postData = array(
            'entry'     => 'weibo',
            'gateway'   => 1,
            'from'      => '',
            'savestate' => 7,
            'useticket' => 1,
            'pagerefer' => '',
            'vsnf'      => 1,
            'su'        => $su,//根据用户名urlencode base_64
            'service'   => 'miniblog',
            'servertime'=> $this->servertime,
            'nonce'     => $this->nonce,
            'pwencode'  => 'rsa2',
            'rsakv'     => $this->rsakv,
            'sp'        => $sp,
            'sr'        => '1440*900',
            'encoding'  => 'UTF-8',
            'prelt'     => 256,
            'url'       => 'http://weibo.com/ajaxlogin.php?framelogin=1&callback=parent.sinaSSOController.feedBackUrlCallBack',
            'returntype'=> 'MATE' // TEXT
            
        );
        //如果需要验证码
        if(!empty($this->pcid))
        {
            $pcCode = $this->readVcode($this->pcid);//此方法识别验证码 详情请见识别验证码方法
            $postData['pcid'] = $this->pcid;
            $postData['door'] = $pcCode;
        }
        $response = $this->curl(self::SINA_WEIBO_LOGIN_URL, $postData);
        if( preg_match('/location\.replace\([\'\"](.*?retcode=(\d+))[\'\"]\)/', $response, $m) && $m[2] === '0' )
        {
            $referer = 'http://login.sina.com.cn/sso/login.php?client=ssologin.js(v1.4.18)';
            $headers = array(
                'Host: passport.weibo.com',
                'Connection: Keep-Alive',
                'Accept-Language: zh-CN,zh;q=0.8',
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8'
            );
            /* 此处不知道什么原因 登录第一次访问 无法保存cookie 第二次才可以 暂没找到解决方法 所以失败了 在访问一次
             * 找到解决方案后 请优化此处并上传代码
             */
            $res = $this->curl($m[1], null, $headers, $referer);
            
            if(!preg_match('/\((\{.*?\})\)/', $res, $match))
            {
                //如果两次都不匹配 则登录失败
                $res = $this->curl($m[1], null, $headers, $referer);
                
                if(!preg_match('/\((\{.*?\})\)/', $res, $match))
                {
                    return false;
                }
            }
            $json = json_decode($match[1], true);
            if(!$json['result'] || empty($json['userinfo']['uniqueid']))
            {
                return false;
            }
            $this->userid = $json['userinfo']['uniqueid'];
            return $json['userinfo']['uniqueid'];
            
            //个人主页
           // $url = 'http://weibo.com/'.$json['userinfo']['userdomain'];
            /*$headers = array(
                'Host: weibo.com',
                'Connection: Keep-Alive',
                'Accept-Language: zh-CN,zh;q=0.8',
                //'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,* /*;q=0.8'
            );
            $res = $this->curl($url, null, $headers);
            */
           
        }else{
            return false;
        }
    }
    
    /**
         * 新浪微博预登陆获取参数.
         *
         * @access public
         * @param  
         * @return boolean
         */
    public function prelogin()
    {
        $data = [
            "entry"   => "sso",
            #不传callback返回的就是纯json数据
            "callback"=> "",
            "su"      => "",
            "rsakt"   => "mod",
            "client"  => "ssologin.js(v1.4.18)",
            "_"       => $this->makeMicrotime(),
        ];
       $response = $this->curl(self::SINA_WEIBO_PRELOGIN_URL.'?'. http_build_query($data));
       if($response)
       {
          $body = json_decode($response, true);
          
          if($body['retcode'] === 0)
          {
              $this->servertime = $body['servertime'];
              $this->pcid       = $body['pcid'];
              $this->nonce      = $body['nonce'];
              $this->pubkey     = $body['pubkey'];
              $this->rsakv      = $body['rsakv'];
              $this->exectime   = $body['exectime'];
              return true;
          }
         
          return false;
       }
       return false;
    }
    
    /**
         * 发布微博 内容以#开头 以#结尾 就是发送话题.
         *
         * @access public
         * @param  string $content 发布微博内容
         * @return boolean
         */
    
    public function addWeibo($content)
    {
        
        //话题内容
        
        //http://weibo.com/aj/mblog/add?ajwvr=6&__rnd=1457070521621
        if(empty($content))
        {
            return false;
        }
        $postData = array(
            'location'      => 'v6_content_home',
            'appkey'        => '',
            'style_type'    => 1,
            'pic_id'        => '',
            'text'          => $content,
            'pdetail'       => '',
            'rank'          => 0,
            'rankid'        => '',
            'module'        => 'stissue',
            'pub_source'    => 'main_',
            'pub_type'      => 'dialog',
            '_t'            => 0
        );
        
        $url = str_replace('%replace%', $this->makeMicrotime(), self::SINA_WEIBO_ADD_WEIBO_URL);
        $postStr = http_build_query($postData);
        $contentLength = strlen($postStr);
        $referer = 'http://weibo.com/u/'.$this->userid.'/home?wvr=5';
        $res = $this->curl($url, $postData, null, $referer);
        $response = json_decode($res, true);
        if($response['code'] === '100000')
        {
            return true;
        }
        return false;
    }
    
    
    /**
         * 获取话题页面内容.
         *
         * @access public
         * @param  string  $topicUrl  话题链接
         * @return array
         */
    public function getTopicContent($topicUrl)
    {
        if( empty($topicUrl) || !preg_match('/(http|https):\/\//i', $topicUrl) )
        {
            return array('flag' => 'error', 'msg' => '话题地址错误', 'data' => null);
        }
       
        //获取话题页面
        $response = $this->curl($topicUrl);
        if(empty($response))
        {
            return array('flag' => 'error', 'msg' => '未获取到话题内容', 'data' => null);
        }
        return array('flag' => 'success', 'msg' => '成功', 'data' => str_replace(array('\r', '\n', '\t'), '', $response));
    }  
    /**
         * 获取话题页面下的微博内容.
         *
         * @access public  
         * @param  string $response getTopicContent返回的话题内容
         * @param  int    $num  获取该话题下最少多少条微博 微博是以ajax瀑布流的方式加载的
         * @return array
         */
    public function getTopicWeibo($response, $num = 100){
        if(empty($response) || !is_int($num))
        {
            return array('flag' => 'error', 'msg' => '内容为空', 'data' => null);
        }
        $func = function($pattern, $response){
            if( preg_match($pattern, $response, $recommend) )
            {
                $recommendJson = json_decode($recommend[1], true);
                //$recommendJson['html']
                $html = $recommendJson['html'];
                //获取微博DIV块
                $pattern = '/<div\s+tbinfo=["\']ouid=\d+["\']\s+class=["\']WB_cardwrap\s+WB_feed_type\s+S_bg2[\s+WB_feed_vipcover]*["\']\s+mid=["\'](?P<mid>\d+)["\']\s+action-type=["\']feed_list_item["\'].*?>/i';
                if( preg_match_all($pattern, $html, $matchAll, PREG_SET_ORDER) )
                {
                    return $matchAll;
                }
            }
            return array();
        };
        
        //主持人推荐相关话题微博
        $pattern = '/<script>FM\.view\((\{["\']ns["\']:["\']pl\.content\.homeFeed\.index["\'],["\']domid["\']:["\']Pl_Third_App__7["\'].*?\})\)/i';
        $recommendResult =  $func($pattern, $response);
        //全部相关话题微博
        $patternAll = '/<script>FM\.view\((\{["\']ns["\']:["\']pl\.content\.homeFeed\.index["\'],["\']domid["\']:["\']Pl_Third_App__9["\'].*?\})\)/i';
        $allResult = $func($patternAll, $response);
        $return = array_merge($recommendResult, $allResult);
        if( count($return) >= $num)
        {
            return array('flag' => 'success', 'msg' => 'success', 'data' => $return);
        }
        //获取参数
        $pattern = '/\$CONFIG\[[\'"]page_id[\'"]\]=[\'"](?P<pageId>.*?)[\'"]/i';
        if( !preg_match($pattern, $response, $match) )
        {
            return array('flag' => 'success', 'msg' => 'pageId获取错误', 'data' => $return);
        }
        $pageId = $match['pageId'];
        if( !preg_match($patternAll, $response, $recommend) )
        {
            return array('flag' => 'success', 'msg' => '获取微博内容块HTML错误', 'data' => $return);
        }
        $funcArg = function($content, &$existsPagebar)
        {
            $json = json_decode($content, true);
            $html = $json['html'] ? $json['html'] :  $json['data'];
            $pattern = '/<div\s+class=["\']WB_cardwrap\s+S_bg2["\']\s*(node-type=["\']lazyload["\']|(?P<notExistsPageBar>bpfilter=["\']page["\']\s*action-type=["\']fl_loadmore["\']))\s*action-data=[\'"](?P<action_data>.*?)[\'"]/i';
            
            if ( !preg_match($pattern, $html, $match) )
            {
                return false;
            }
            if(!empty($match['notExistsPageBar']))
            {
                $existsPagebar = false;
            }else{
                $existsPagebar = true;
            }
            $match['action_data'] = htmlspecialchars_decode($match['action_data']);
            parse_str($match['action_data'], $res);
            return $res;
        };
        $pagebarFlag = true;
        $arg = $funcArg($recommend[1], $pagebarFlag);
        if(!$arg)
        {
            return array('flag' => 'success', 'msg' => '获取参数错误', 'data' => $return);
        }
        //初始参数
        $pagebar = 0;
        $argArray = array(
            'ajwvr'        => 6,
            'domain'       => 100808,
            'pagebar'      => $pagebar, //pagebar参数 无 0 1 循环
            'tab'          => $arg['tab'] ? $arg['tab'] : '',//获取
            'current_page' => $arg['current_page'],//获取
            'since_id'     => $arg['since_id'],//获取
            'page'         => $arg['page'],//获取    
            'pre_page'     => $arg['pre_page'],//获取
            'pl_name'      => 'Pl_Third_App__9',//固定
            'domain_op'    =>'100808',//固定
            '__rnd'        => $this->makeMicrotime(),
            'id'           => $pageId,//页面获取固定
            'script_uri'   => '/p/'.$pageId,
            'feed_type'    => 1,
            'pids'         => $arg['pids'] ? $arg['pids'] : '',
        );
        $url = 'http://weibo.com/p/aj/v6/mblog/mbloglist';
        $referer = 'http://weibo.com/p/'.$pageId;
        $header = array(
            'X-Requested-With:XMLHttpRequest'
        );
        
        $funcPageContent = function($response)
        {
            $json = json_decode($response, true);
            if( empty($json['data']) || $json['code'] != '100000' )
            {
                return false;
            }
            $data = str_replace(array('\r', '\n', '\t'), '', $json['data']);
            $pattern = '/<div\s+tbinfo=["\']ouid=\d+["\']\s+class=["\']WB_cardwrap\s+WB_feed_type\s+S_bg2[\s+WB_feed_vipcover]*["\']\s+mid=["\'](?P<mid>\d+)["\']\s+action-type=["\']feed_list_item["\'].*?>/i';
            if( preg_match_all($pattern, $data, $matchAll, PREG_SET_ORDER) )
            {
                return $matchAll;
            }
            return false;
        };
        //当话题页微博少于规定的数量时候 去获取ajax数据
        while( count($return) < $num)
        {
            $result = $this->curl($url.'?'.http_build_query($argArray), '', $header, $referer);
            if(!$result)
            {
                return array('flag' => 'success', 'msg' => '获取分页数据错误', 'data' => $return);
            }
            
            $res = json_decode($result, true);
            $returnArray = $funcPageContent($result);
            if(empty($returnArray))
            {
                return array('flag' => 'success', 'msg' => '获取分页微博数据错误', 'data' => $return);
            }
            $return  = array_merge($return, $returnArray);
            //覆盖参数
            $arg = $funcArg($result, $pagebarFlag);
            if(!$arg)
            {
                return array('flag' => 'success', 'msg' => '获取分页参数错误', 'data' => $return);
            }
            
            $argArray = array(
                'ajwvr'        => 6,
                'domain'       => 100808,
                'tab'          => $arg['tab'] ? $arg['tab'] : '',//获取
                'current_page' => $arg['current_page'],//获取
                'since_id'     => $arg['since_id'],//获取
                'page'         => $arg['page'],//获取
                'pre_page'     => $arg['pre_page'],//获取
                'pl_name'      => 'Pl_Third_App__9',//固定
                'domain_op'    =>'100808',//固定
                '__rnd'        => $this->makeMicrotime(),
                'id'           => $pageId,//页面获取固定
                'script_uri'   => '/p/'.$pageId,
                'feed_type'    => 1,
                'pids'         => $arg['pids'] ? $arg['pids'] : '',
            );
            if ( $pagebarFlag === true)
            {
                $argArray['pagebar'] = ++$pagebar;
            }else{
                $pagebar = -1;
            }
        }
        
        return array('flag' => 'success', 'msg' => 'success', 'data' => $return);
    }
    
    
    
    /**
         * 关注话题.
         *
         * @access public
         * @param  string $response  getTopicContent返回的话题页面内容
         * @return array
         */
    public function followTopic($response)
    {
        if( empty($response) )
        {
            return array('flag' => 'error', 'msg' => '内容为空', 'data' => null);
        }
        
        //匹配关注按钮 获取参数
        $pattern = '/<div\s+class=\\\\"btn_bed W_fl\\\\"\s+node-type=\\\\"followBtnBox\\\\"\s+action-data=\\\\"(.*?)\\\\">/';
        if( !preg_match($pattern, $response, $match) )
        {
            return array('flag' => 'error', 'msg' => '匹配关注按钮失败，无法获取参数', 'data' => null);
        }
        parse_str($match[1], $array);
        $postData = array(
            'uid'        => $array['uid'],
            'objectid'   => $array['objectid'],
            'f'          => $array['f'],
            'extra'      => '',
            'refer_sort' => '',
            'refer_flag' => '',
            'location'   => 'page_100808_home',
            'oid'        => substr($array['objectid'], -32),
            'wforce'     => 1,
            'nogroup'    => $array['nogroup'],
            'fnick'      => $array['fnick'],
            'template'   => $array['template'],
            'isinterest' => $array['isinterest'],
            '_t'         => 0
        );
        $url = str_replace('%replace%', $this->makeMicrotime(), self::SINA_WEIBO_FOLLOW_URL);
        $referer = 'http://weibo.com';
        $followRes = $this->curl($url, $postData, '', $referer);
        if(!$followRes)
        {
            return array('flag' => 'error', 'msg' => '关注失败', 'data' => null);
        }
        $res = json_decode($followRes, true);
        if($res['code'] !== '100000')
        {
            return array('flag' => 'error', 'msg' => '关注失败,返回值不为100000', 'data' => null);
        }
        
        return array('flag' => 'success', 'msg' => '关注成功', 'data' => null);
    }
    
    
    
    
    /**
     * 获取实时热搜、热点热搜话题 
     *
     * @access public
     * @param
     * @return array
     */
    public function getHot()
    {
        $res = $this->curl(self::SINA_WEIBO_HOT_URL);
        if(empty($res))
        {
            return false;
        }
        $pattern = '/\((\{[\"\']pid[\"\']:[\"\']pl_top_homepage[\"\'].*?\})\)/i';
        
        if(!preg_match($pattern, $res, $match))
        {
            return false;
        }
        //去除\n
        $match[1] = str_replace('\n', '', $match[1]);
            
        $array = json_decode($match[1], true);
        if(!is_array($array) || !$array['html']) return false;
        
        $html = $array['html'];
        $result = array();
        
        $func = function($pattern, $html)
        {
            if(preg_match($pattern, $html, $match) )
            {
                $content = $match[0];
                //继续匹配内容
                $p = '/<p class=["\']star_name["\']>.*?<a.*?>(.*?)<\/a>/i';
                if( preg_match_all($p, $content, $return) )
                {
                    return $return[1];
                }
            }
            return false;
        };
        //实时热搜
        $pattern = '/<table tab=[\"\']realtimehot[\"\'] id=[\"\']realtimehot[\"\'].*?>.*?<\/table>/i';
        $result['realtime'] = $func($pattern, $html);
        //热点热搜
        $pattern = '/<table tab=[\"\']all[\"\'] id=[\"\']all[\"\'].*?>.*?<\/table>/i';
        $result['all'] = $func($pattern, $html);
        return $result;
    }
    
    /**
         * 一小时热点和24小时热点.
         *
         * @access public
         * @param  
         * @return array
         */
    public function getNow()
    {
        $time = $this->makeMicrotime();
        $url_1 = str_replace(array(
            '#type#',
            '#time#'
        ), array(
            '0',
            $time
        ), self::SINA_WEIBO_NOW_URL);
        
        $url_24 = str_replace(array(
            '#type#',
            '#time#'
        ), array(
            '1',
            $time
        ), self::SINA_WEIBO_NOW_URL);
        $return = array();
       
        
        $func = function($content)
        {
            //去除 \r\n\t
            $content = str_replace(array('\r','\n','\t'), '', $content);
            file_put_contents('./now.html', $content);
            $pattern = '/\((\{.*?\})\)/i';
            if(!preg_match($pattern, $content, $match))
            {
                return false;
            }
            
            $array = json_decode($match[1],true);
            if(empty($array['html']))
            {
                return false;
            }
            //echo $array['html'];
            $pattern = '/<div class="pic_box">\S*<a.*?>\S*<img.*?alt=["\'](.*?)["\']/i';
            if( !preg_match_all($pattern, $array['html'], $result))
            {
                return false;
            }
            return $result[1];
        };
        //一小时热点
        $res_1  = $this->curl($url_1, '', '', $url_24);
        if(!empty($res_1))
        {
           $return[1] = $func($res_1);
        }
        //24小时热点
        $res_24 = $this->curl($url_24, '', '', $url_1);
        if(!empty($res_24))
        {
            $return[24] = $func($res_24);
        }
        return $return;
    }
    
    
    /**
         * 微博点赞. 
         *
         * @access public
         * @param  int     $mid 微博mid
         * @return boolean
         */
    public function beLike($mid)
    {
        if(!preg_match('/\d+/', $mid))
        {
            return false;
        }
        $mid = strval($mid);
        $url = str_replace('#time#', $this->makeMicrotime(), self::SINA_WEIBO_BELIKE_URL);
        
        $postData = array(
            'mid'       => $mid,
            'location'  => '',
            '_t'        => 0
        );
        $header = array();
        $referer = 'http://weibo.com';
        $response = $this->curl($url, $postData, $header, $referer);
        $json = json_decode($response, true);
        if($json['code'] == '100000')
        {
            return true;
        }
        return false;
    }
    /**
         * 搜索微博. 并返回微博结果数组
         *
         * @access public
         * @param  string   $content   搜索内容
         * @param  int   $page    第几页
         * @return array
         */
    public function search($content, $page = 1)
    {
        if(empty($content))
        {
            return false;
        }
        $content = urlencode(urlencode($content));
        $url = str_replace(array('#content#', '#num#'), array($content, $page), self::SINA_WEIBO_SEARCH_URL);
        $header = array(
            'Host: s.weibo.com',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Upgrade-Insecure-Requests: 1',
            'Accept-Language: zh-CN,zh;q=0.8'
        );
        $referer = 'http://s.weibo.com/';
        $response = $this->curl($url, '', $header, $referer);
        if(empty($response)) return false;
        
        $pattern = '/<script>STK && STK\.pageletM && STK\.pageletM\.view\((\{["\']pid["\']:["\']pl_weibo_direct["\'].*?\})\)<\/script>/i';
        if( !preg_match($pattern, $response, $match) )
        {
            return false;
        }
        //去除 \r\n\t
        $match[1] = str_replace(array('\r','\n','\t'), '', $match[1]);
        $json = json_decode($match[1], true);
        if(empty($json['html']))
        {
            return false;
        }
        
        $pattern = '/<div\s+mid=["\'](?P<mid>\d+)["\']\s+action-type=[\'"]feed_list_item[\'"]\s*>.*?<div\s+class=["\']content clearfix["\']\s+node-type=["\']like["\']\s*>.*?<\/ul/i';
        if ( preg_match_all($pattern, $json['html'], $matchAll, PREG_SET_ORDER) )
        {
            return $matchAll;
        }
        return false;
    }
    
    /**
         * 评论微博
         *
         * @access public
         * @param  int    $userid 用户登录后获取的用户id
         * @param  int    $mid   微博的mid 
         * @return boolean
         */
    public function comment($mid, $content)
    {
        if(!preg_match('/^\d+$/', $this->userid) || ! preg_match('/^\d+$/', $mid) || empty($content)) return false;
        $url = str_replace('#time#', $this->makeMicrotime(), self::SINA_WEIBO_COMMENT_URL);
        
        $postData = array(
            'act'       => 'post',
            'mid'       => strval($mid), 
            'uid'       => strval($this->userid), 
            'forward'   => 0,
            'isroot'    => 0,
            'content'   => $content,
            'pageid'    => 'weibo',
            '_t'        => 0
        );
        $referer = 'http://s.weibo.com/';
        $header = array(
            'X-Requested-With:XMLHttpRequest'
        );
        $res =  $this->curl($url, $postData, $header, $referer);
        if(empty($res))
        {
            return false;
        }
        $result = json_decode($res, true);
        if(!isset($result['code']) || $result['code'] != '100000')
        {
            return false;
        }
        return true;
    }
    
    /**
         * 转发微博.
         *
         * @access public
         * @param  int     name
         * @return boolean
         */
    public function forword($mid, $content = '')
    {
        
        if( !preg_match('/^\d+$/', $this->userid) || ! preg_match('/^\d+$/', $mid) ) return false;
        $url = str_replace(array('#userid#', '#time#'), array($this->userid, $this->makeMicrotime()), self::SINA_WEIBO_FORWARD_URL);
        
        $postData = array(
            'pic_src'        => '',
            'pic_id'         => '',
            'appkey'         => '',
            'mid'            => strval($mid),
            'style_type'     => 1,
            'mark'           => '',
            'reason'         => $content,
            'location'       => 'v6_content_home',
            'pdetail'        => '',
            'module'         => '',
            'page_module_id' => '',
            'refer_sort'     => '',
            'rank'           => 0,
            'rankid'         => '',
            '_t'             => 0
        );
        $header = array(
            'X-Requested-With:XMLHttpRequest',
        );
        $referer = 'http://weibo.com/u/'.$this->userid.'/home?leftnav=1';
        $response = $this->curl($url, $postData, $header, $referer);
        if(empty($response)) return false;
        $result = json_decode($response, true);
        if(!isset($result['code']) || $result['code'] != '100000')
        {
            return false;
        }
        return true;
    }
    
    /**
     * 设置自动回复. 关注自动回复和私信自动回复
     *
     * @access public
     * @param  int     name
     * @return boolean
     */
    public function autoReply($content)
    {
        //同意协议
        $url = 'http://e.weibo.com/v1/public/groupmsg/main?clicked=1&follow=1&share=1';
    
        $referer = 'http://e.weibo.com/';
        $data = array();
        $header = array();
        $response = $this->curl($url, $data, $header, $referer);
        if(empty($response))
        {
            return false;
        }
    
        //设置关注自动回复
        $url = 'http://e.weibo.com/aj/publicplatform/checksensitive';
        $referer = 'http://e.weibo.com/';
        $header = array(
            'X-Requested-With:XMLHttpRequest'
        );
        $data = array(
            'context'        => $content,
            'check_level'    => 3,
            'check_type'     => 'text',
            'cache_type'     => 1,
            'checkSensitive' => 'true',
            '_t'             => 0
        );
        $res = $this->curl($url, $data, $header, $referer);
        $json = json_decode($res, true);
    
        $setUrl = 'http://e.weibo.com/v1/public/aj/autoreply/setautoreply';
        if($json['code'] == '100000')
        {
            $data = array(
                'text'   => $content,
                'reason' => 'followme',
                'type'   => 'text',
                '_t'     => 0
            );
            $followRes = $this->curl($setUrl, $data, $header, $referer);
            $followRes = json_decode($followRes, true);
        }
        //私信自动回复
        $data = array(
            'context'        => $content,
            'check_level'    => 3,
            'check_type'     => 'text',
            'cache_type'     => 2,
            'checkSensitive' => 'true',
            '_t'             => 0
        );
        $res = $this->curl($url, $data, $header, $referer);
        $json = json_decode($res, true);
        if($json['code'] == '100000')
        {
            $data = array(
                'text'   => $content,
                'reason' => 'message',
                'type'   => 'text',
                '_t'     => 0
            );
            $mesRes = $this->curl($setUrl, $data, $header, $referer);
            $mesRes = json_decode($mesRes, true);
        }
        return array(
            'followResponse'  => $followRes['code'] == '100000' ? true : false,
            'messageResponse' => $mesRes['code'] == '100000' ? true : false
        );
    }
    
    /**
     * 获取单条微博 匹配微博mid.
     *
     * @access public
     * @param  string   $url  微博链接
     * @return boolean
     */
    public function getWeiboMid($url)
    {
        if(empty($url) || !preg_match('/^(http|https):\/\//i',$url))
        {
            return false;
        }
        $weiboRes = $this->curl($url);
        //过滤\n \r \t
        $content = str_replace(array('\r', '\n', '\t'), '', $weiboRes);
        $pattern = '/<div\s*tbinfo=.*?diss-data=.*?mid=\\\\[\'"](?P<mid>.*?)\\\\[\'"]/i';
        if ( preg_match($pattern, $content, $match) )
        {
            return $match['mid'];
        }
        return false;
    }
    /**
         * 随机获取一个表情 .
         *
         * @access public
         * @param  
         * @return string
         */
    public function getFace()
    {
        return $this->allFace[array_rand($this->allFace)];
    }
    
    protected function makeMicrotime()
    {
        
        list($microtime, $time) = explode(' ', microtime()); 
        return floor(($time+$microtime)*1000);
    }
    
   
    
    
    protected function curl($url, $data = array(), $header = array(), $referer = '') {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(!empty($header))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        if(!empty($referer))
        {
            curl_setopt($ch, CURLOPT_REFERER, $referer);
        }
        curl_setopt($ch,CURLOPT_TIMEOUT,70);
        curl_setopt($ch,CURLOPT_AUTOREFERER,true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36');
        if(!empty($this->cookieFile))
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieFile);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieFile);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        if ($data) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    
    
    
    /**
         * 读取验证码.
         *
         * @access public
         * @param  int     name
         * @return boolean
         */
    public function readVcode($pcid)
    {
        /**
         * 目前市面上已经有非常多成熟的验证码识别程序 请自己寻找一个并实现此处验证码识别代码
         * 
         * 
         */
        return 'vcode';
    }
}