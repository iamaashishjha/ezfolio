import React, { useEffect, useState } from 'react';
import { Button, Space, Modal, Row, Col, Card, DatePicker, Statistic, Empty, List, Table, Tag, Typography, Tooltip } from 'antd';
import moment from 'moment';
import { useIsMounted } from '../../../common/hooks/IsMounted';
import HTTP from '../../../common/helpers/HTTP';
import Routes from '../../../common/helpers/Routes';
import Utils from '../../../common/helpers/Utils';
import { useSelector } from 'react-redux';
import { 
    TeamOutlined, 
    UserAddOutlined, 
    UserSwitchOutlined, 
    ExclamationCircleOutlined
} from '@ant-design/icons';
import styled from 'styled-components';
import { Pie, WordCloud } from '@ant-design/charts';

const { confirm } = Modal;
const { Text } = Typography;

const pieConfig = {
    appendPadding: 10,
    angleField: 'value',
    colorField: 'name',
    radius: 1,
    height: 200,
    innerRadius: 0.6,
    label: {
        type: 'inner',
        autoRotate: false,
        offset: '-50%',
        content: function content(_ref) {
            let percent = _ref.percent;
            return ''.concat(parseFloat(percent * 100).toFixed(0), '%');
        },
        style: {
            textAlign: 'center',
            fontSize: 14,
        },
    },
    interactions: [{ type: 'element-selected' }, { type: 'element-active' }],
    statistic: {
        title: false,
        content: {
            style: {
                whiteSpace: 'pre-wrap',
                overflow: 'hidden',
                textOverflow: 'ellipsis',
            },
            formatter: function formatter() {
                return '';
            },
        },
    },
};

const wordCloudConfig = {
    wordField: 'name',
    weightField: 'value',
    colorField: 'name',
    wordStyle: {
        fontFamily: 'Verdana',
        fontSize: [8, 32],
        rotation: 0,
    },
    random: function random() {
        return 0.5;
    },
};

const StyledCol = styled(Col)`
text-align: ${props => props.align ? props.align : 'left'};
@media (max-width: 768px) {
    text-align: center !important;
    ${props => props.mobilePaddingTop && `padding-top: ${props.mobilePaddingTop};`}
}
`;

const Visitors = () => {
    const isMounted = useIsMounted();
    const { demoMode } = useSelector(state => state.globalState);
    const [loading, setLoading] = useState(false);

    const [date, setDate] = useState({
        startDate: null,
        endDate: null,
    });

    const [visitorsData, setVisitorsData] = useState([{
        total: 0,
        new: 0,
        old: 0,
    }]);

    const [locationData, setLocationData] = useState([]);
    const [deviceData, setDeviceData] = useState([]);
    const [browserData, setBrowserData] = useState([]);
    const [platformData, setPlatformData] = useState([]);
    const [countryStats, setCountryStats] = useState([]);
    const [regionStats, setRegionStats] = useState([]);
    const [regionDeviceStats, setRegionDeviceStats] = useState([]);
    const [ipStats, setIpStats] = useState({
        unique: 0,
        top: []
    });
    const [cityStats, setCityStats] = useState({
        unique: 0,
        top: []
    });
    const [recentVisitors, setRecentVisitors] = useState([]);

    useEffect(() => {
        loadData();
    }, []);

    useEffect(() => {
        if (isMounted) {
            loadData();
        }
    }, [date]);

    const loadData = (_loading = true) => {
        setLoading(_loading);

        HTTP.get(Routes.api.admin.visitorsStats, {
            params: {
                startDate: date.startDate,
                endDate: date.endDate,
            }
        })
        .then(response => {
            Utils.handleSuccessResponse(response, () => {
                const result = response.data.payload;

                if (result) {
                    //visitors
                    setVisitorsData({
                        total: parseInt(result.visitors.total),
                        new: parseInt(result.visitors.new),
                        old: parseInt(result.visitors.old)
                    });

                    //location
                    let locationArray = [];
                    result.location.forEach(element => {
                        locationArray.push({
                            name: element.location,
                            value: parseInt(element.total)
                        })
                    });
                    setLocationData(locationArray);

                    //device
                    let deviceArray = [];

                    if (parseInt(result.device.desktop)) {
                        deviceArray.push({
                            name: "Desktop",
                            value: parseInt(result.device.desktop)
                        })
                    }
                    
                    if (parseInt(result.device.mobile)) {
                        deviceArray.push({
                            name: "Mobile",
                            value: parseInt(result.device.mobile)
                        })
                    }

                    setDeviceData(deviceArray);

                    //browser
                    let browserArray = [];
                    result.browser.forEach(element => {
                        browserArray.push({
                            name: element.browser,
                            value: parseInt(element.total)
                        })
                    });
                    setBrowserData(browserArray);

                    //platform
                    let platformArray = [];
                    result.platform.forEach(element => {
                        platformArray.push({
                            name: element.platform,
                            value: parseInt(element.total)
                        })
                    });
                    setPlatformData(platformArray);

                    //country stats
                    setCountryStats(result.country && Array.isArray(result.country.top) ? result.country.top : []);

                    //region stats
                    setRegionStats(result.region && Array.isArray(result.region.top) ? result.region.top : []);

                    //region device stats
                    const regionDeviceRows = result.region_device && Array.isArray(result.region_device) ? result.region_device : [];
                    const regionDeviceMap = {};
                    regionDeviceRows.forEach(row => {
                        const region = row.region || 'Unknown';
                        const count = parseInt(row.total, 10) || 0;
                        if (!regionDeviceMap[region]) {
                            regionDeviceMap[region] = {
                                region,
                                desktop: 0,
                                mobile: 0,
                                total: 0,
                            };
                        }

                        if (row.is_desktop === 1 || row.is_desktop === '1' || row.is_desktop === true) {
                            regionDeviceMap[region].desktop += count;
                        } else if (row.is_desktop === 0 || row.is_desktop === '0' || row.is_desktop === false) {
                            regionDeviceMap[region].mobile += count;
                        }

                        regionDeviceMap[region].total += count;
                    });
                    setRegionDeviceStats(Object.values(regionDeviceMap).sort((a, b) => b.total - a.total));

                    //ip stats
                    const ipUnique = result.ip && typeof result.ip.unique !== 'undefined' ? parseInt(result.ip.unique) : 0;
                    setIpStats({
                        unique: ipUnique,
                        top: result.ip && Array.isArray(result.ip.top) ? result.ip.top : []
                    });

                    //city stats
                    const cityUnique = result.city && typeof result.city.unique !== 'undefined' ? parseInt(result.city.unique) : 0;
                    setCityStats({
                        unique: cityUnique,
                        top: result.city && Array.isArray(result.city.top) ? result.city.top : []
                    });

                    //recent visitors
                    setRecentVisitors(Array.isArray(result.recent) ? result.recent : []);
                }
            });
        })
        .catch(error => {
            Utils.handleException(error);
        }).finally(() => {
            setLoading(false);
        });
    }

    const datePickerOnChange = (dates) => {
        let utcStartDate = null;
        let utcEndDate = null;

        if (dates) {
            let withoutUtcStartDate = dates[0];
            let withoutUtcEndDate = dates[1];

            utcStartDate =  moment.utc(withoutUtcStartDate.startOf('day')).format('YYYY-MM-DD HH:mm:ss');
            utcEndDate = moment.utc(withoutUtcEndDate.endOf('day')).format('YYYY-MM-DD HH:mm:ss');
        }

        setDate({
            startDate: utcStartDate,
            endDate: utcEndDate,
        });
    }

    const showResetConfirm = () => {
        if (demoMode) {
            Utils.showNotification('This feature is disabled in demo', 'warning');
        } else {
            confirm({
                confirmLoading: loading,
                title: 'Are you sure?',
                content: 'By pressing OK, all stats related to visitors will be removed. Please proceed with cautions.',
                icon: <ExclamationCircleOutlined />,
                mask: true,
                onOk() {
                    setLoading(true);

                    HTTP.delete(Routes.api.admin.visitorsStats)
                    .then(response => {
                        Utils.handleSuccessResponse(response, () => {
                            //visitors
                            setVisitorsData({
                                total: 0,
                                new: 0,
                                old: 0
                            });

                            //location
                            setLocationData([]);

                            //device
                            setDeviceData([]);

                            //browser
                            setBrowserData([]);

                            //platform
                            setPlatformData([]);

                            //country stats
                            setCountryStats([]);

                            //region stats
                            setRegionStats([]);

                            //region device stats
                            setRegionDeviceStats([]);

                            //ip stats
                            setIpStats({
                                unique: 0,
                                top: []
                            });

                            //city stats
                            setCityStats({
                                unique: 0,
                                top: []
                            });

                            //recent visitors
                            setRecentVisitors([]);

                            Utils.showNotification(response.data.message, 'success', false);
                        });
                    })
                    .catch((error) => {
                        Utils.handleException(error);
                    }).finally(() => {
                        setLoading(false);
                    });
                },
            });
        }
    }

    const formatLocation = (record) => {
        const parts = [record.city, record.region, record.location].filter(part => part && part !== 'Unknown');
        if (parts.length) {
            return parts.join(', ');
        }
        return record.location || 'Unknown';
    };

    const renderTopList = (data, valueKey) => {
        if (!data.length) {
            return <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />;
        }

        return (
            <List
                size="small"
                dataSource={data}
                renderItem={(item) => (
                    <List.Item>
                        <div style={{ display: 'flex', width: '100%', justifyContent: 'space-between' }}>
                            <Text>{item[valueKey] || 'Unknown'}</Text>
                            <Text type="secondary">{item.total}</Text>
                        </div>
                    </List.Item>
                )}
            />
        );
    };

    const recentColumns = [
        {
            title: 'IP',
            dataIndex: 'ip',
            key: 'ip',
            width: 160,
            render: (value) => value || 'Unknown',
        },
        {
            title: 'Location',
            key: 'location',
            render: (_, record) => (
                <Space direction="vertical" size={0}>
                    <Text>{formatLocation(record)}</Text>
                    {record.timezone ? <Text type="secondary">{record.timezone}</Text> : null}
                </Space>
            ),
        },
        {
            title: 'Device',
            dataIndex: 'is_desktop',
            key: 'device',
            width: 120,
            render: (value) => {
                let label = 'Unknown';
                let color = 'default';

                if (value === 1 || value === '1' || value === true) {
                    label = 'Desktop';
                    color = 'geekblue';
                } else if (value === 0 || value === '0' || value === false) {
                    label = 'Mobile';
                    color = 'green';
                }

                return <Tag color={color}>{label}</Tag>;
            },
        },
        {
            title: 'Browser',
            dataIndex: 'browser',
            key: 'browser',
            render: (value) => value || 'Unknown',
        },
        {
            title: 'Platform',
            dataIndex: 'platform',
            key: 'platform',
            render: (value) => value || 'Unknown',
        },
        {
            title: 'Visited',
            dataIndex: 'created_at',
            key: 'created_at',
            width: 140,
            render: (value) => (
                value ? (
                    <Tooltip title={moment(value).format('YYYY-MM-DD HH:mm:ss')}>
                        {moment(value).fromNow()}
                    </Tooltip>
                ) : 'Unknown'
            ),
        },
        {
            title: 'Type',
            dataIndex: 'is_new',
            key: 'is_new',
            width: 120,
            render: (value) => {
                let label = 'Unknown';
                let color = 'default';

                if (value === 1 || value === '1' || value === true) {
                    label = 'New';
                    color = 'blue';
                } else if (value === 0 || value === '0' || value === false) {
                    label = 'Returning';
                }

                return <Tag color={color}>{label}</Tag>;
            },
        },
    ];

    const regionDeviceColumns = [
        {
            title: 'Region',
            dataIndex: 'region',
            key: 'region',
            render: (value) => value || 'Unknown',
        },
        {
            title: 'Desktop',
            dataIndex: 'desktop',
            key: 'desktop',
            width: 120,
        },
        {
            title: 'Mobile',
            dataIndex: 'mobile',
            key: 'mobile',
            width: 120,
        },
        {
            title: 'Total',
            dataIndex: 'total',
            key: 'total',
            width: 120,
        },
    ];

    return (
        <React.Fragment>
            <Row gutter={24}>
                <Col
                    xl={24}
                    lg={24}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Card 
                        bordered={false}
                        hoverable
                        style={{cursor: 'default'}}
                        className="z-shadow"
                    >
                        <Row>
                            <StyledCol md={12} sm={12} xs={24} align={'left'}>
                                <DatePicker.RangePicker
                                    bordered={false}
                                    ranges={{
                                        "Today": [moment(), moment()],
                                        "Yesterday": [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
                                        "This Week": [moment().startOf('week'), moment().endOf('week')],
                                        "Last 7 Days": [moment().subtract(7, 'day'), moment()],
                                        "This Month": [moment().startOf('month'), moment().endOf('month')],
                                        "Last Month": [moment().subtract(1,'months').startOf('month'), moment().subtract(1,'months').endOf('month')],
                                        "Last 30 Days": [moment().subtract(30, 'day'), moment()],
                                    }}
                                    onChange={datePickerOnChange}
                                />
                            </StyledCol>
                            <StyledCol md={12} sm={12} xs={24} align={'right'} mobilePaddingTop={'1rem'}>
                                <Space>
                                    <Button type="primary" danger onClick={showResetConfirm} disabled={loading}>Reset All Stats</Button>
                                    <Button type="primary" onClick={loadData} disabled={loading}>Refresh</Button>
                                </Space>
                            </StyledCol>
                        </Row>
                    </Card>
                </Col>
                <Col
                    xl={12}
                    lg={12}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Row>
                        <Col
                            xl={24}
                            lg={24}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Count"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                <Row>
                                    <Col md={8} sm={12} xs={24}>
                                        <Statistic
                                            className="text-center"
                                            title={'Total Visitors'}
                                            value={visitorsData.total}
                                            prefix={<TeamOutlined />}
                                        />
                                    </Col>
                                    <Col md={8} sm={12} xs={24}>
                                        <Statistic
                                            className="text-center"
                                            title={'New Visitors'}
                                            value={visitorsData.new}
                                            prefix={<UserAddOutlined />}
                                        />
                                    </Col>
                                    <Col md={8} sm={12} xs={24}>
                                        <Statistic
                                            className="text-center"
                                            title={'Returning Visitors'}
                                            value={visitorsData.old}
                                            prefix={<UserSwitchOutlined />}
                                        />
                                    </Col>
                                </Row>
                            </Card>
                        </Col>
                        <Col
                            xl={24}
                            lg={24}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Platform"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {
                                    platformData.length !== 0 ? (
                                        <Pie 
                                            {...pieConfig} 
                                            data={platformData} 
                                        />
                                    ) : (
                                        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                    )
                                }
                            </Card>
                        </Col>
                        <Col
                            xl={24}
                            lg={24}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Browser"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {
                                    browserData.length !== 0 ? (
                                        <Pie 
                                            {...pieConfig} 
                                            data={browserData} 
                                        />
                                    ) : (
                                        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                    )
                                }
                            </Card>
                        </Col>
                    </Row>
                </Col>
                <Col
                    xl={12}
                    lg={12}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Row>
                        <Col
                            xl={24}
                            lg={24}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Location"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {
                                    locationData.length !== 0 ? (
                                        <WordCloud {...wordCloudConfig} data={locationData} height={391}/>
                                    ) : (
                                        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                    )
                                }
                            </Card>
                        </Col>
                        <Col
                            xl={24}
                            lg={24}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Device"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {
                                    deviceData.length !== 0 ? (
                                        <Pie 
                                            {...pieConfig} 
                                            data={deviceData} 
                                        />
                                    ) : (
                                        <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                                    )
                                }
                            </Card>
                        </Col>
                    </Row>
                </Col>
                <Col
                    xl={24}
                    lg={24}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Row gutter={24}>
                        <Col
                            xl={12}
                            lg={12}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Top Countries"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {renderTopList(countryStats, 'location')}
                            </Card>
                        </Col>
                        <Col
                            xl={12}
                            lg={12}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Top Regions"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                {renderTopList(regionStats, 'region')}
                            </Card>
                        </Col>
                    </Row>
                </Col>
                <Col
                    xl={24}
                    lg={24}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Card
                        style={{cursor: 'default'}}
                        title={"Device by Region"}
                        loading={loading}
                        bordered={false}
                        hoverable
                        className="z-shadow"
                    >
                        {
                            regionDeviceStats.length !== 0 ? (
                                <Table
                                    size="small"
                                    rowKey="region"
                                    dataSource={regionDeviceStats}
                                    columns={regionDeviceColumns}
                                    pagination={false}
                                    scroll={{ x: 520 }}
                                />
                            ) : (
                                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                            )
                        }
                    </Card>
                </Col>
                <Col
                    xl={24}
                    lg={24}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Row gutter={24}>
                        <Col
                            xl={12}
                            lg={12}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"IP Insights"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                <div style={{ marginBottom: 16 }}>
                                    <Statistic
                                        title={'Unique IPs'}
                                        value={ipStats.unique}
                                    />
                                </div>
                                {renderTopList(ipStats.top, 'ip')}
                            </Card>
                        </Col>
                        <Col
                            xl={12}
                            lg={12}
                            md={24}
                            sm={24}
                            xs={24}
                            style={{
                                marginBottom: 24,
                            }}
                        >
                            <Card
                                style={{cursor: 'default'}}
                                title={"Top Cities"}
                                loading={loading}
                                bordered={false}
                                hoverable
                                className="z-shadow"
                            >
                                <div style={{ marginBottom: 16 }}>
                                    <Statistic
                                        title={'Unique Cities'}
                                        value={cityStats.unique}
                                    />
                                </div>
                                {renderTopList(cityStats.top, 'city')}
                            </Card>
                        </Col>
                    </Row>
                </Col>
                <Col
                    xl={24}
                    lg={24}
                    md={24}
                    sm={24}
                    xs={24}
                    style={{
                        marginBottom: 24,
                    }}
                >
                    <Card
                        style={{cursor: 'default'}}
                        title={"Recent Visitors"}
                        loading={loading}
                        bordered={false}
                        hoverable
                        className="z-shadow"
                    >
                        {
                            recentVisitors.length !== 0 ? (
                                <Table
                                    size="small"
                                    rowKey="id"
                                    dataSource={recentVisitors}
                                    columns={recentColumns}
                                    pagination={false}
                                    scroll={{ x: 900 }}
                                />
                            ) : (
                                <Empty image={Empty.PRESENTED_IMAGE_SIMPLE} />
                            )
                        }
                    </Card>
                </Col>
            </Row>
        </React.Fragment>
    )
}

export default Visitors;
