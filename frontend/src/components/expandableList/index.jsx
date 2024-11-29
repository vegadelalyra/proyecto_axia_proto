import React, { useState } from 'react';
import { ReactComponent as ExpandIcon } from '../../assets/icons/expand.svg';
import './ExpandableList.sass';

const ExpandableList = ({ data }) => {
  const [expandedTitles, setExpandedTitles] = useState({});
  const [selectedSubtitle, setSelectedSubtitle] = useState(null);
  const [allExpanded, setAllExpanded] = useState(false);

  const toggleTitle = title => {
    setExpandedTitles(prev => ({
      ...prev,
      [title]: !prev[title],
    }));
  };

  const toggleAll = () => {
    const nextExpandState = !allExpanded;
    setAllExpanded(nextExpandState);
    const newExpandedStates = {};
    data.forEach(item => {
      newExpandedStates[item.title] = nextExpandState;
    });
    setExpandedTitles(newExpandedStates);
  };

  return (
    <div className='expandable-list'>
      <ExpandIcon className='global-expand-icon' onClick={toggleAll} />
      {data.map(item => (
        <div key={item.title} className='title-container'>
          <div className='title' onClick={() => toggleTitle(item.title)}>
            {item.title}
          </div>
          {expandedTitles[item.title] && (
            <div className='subtitles'>
              {item.subtitles.map((subtitle, index) => (
                <div
                  key={index}
                  className={`subtitle ${
                    selectedSubtitle === subtitle ? 'selected' : ''
                  }`}
                  onClick={() => setSelectedSubtitle(subtitle)}>
                  {subtitle}
                </div>
              ))}
            </div>
          )}
        </div>
      ))}
    </div>
  );
};

export default ExpandableList;
